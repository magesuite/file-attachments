<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Model;

class FileUploader
{
    protected \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase;

    protected \Magento\Framework\Filesystem\Directory\WriteInterface $directory;

    protected \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory;

    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    protected \Psr\Log\LoggerInterface $logger;

    protected string $baseTmpPath;

    protected string $basePath;

    protected array $allowedExtensions;

    protected array $allowedMimeTypes;

    public function __construct(
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDatabase,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        string $baseTmpPath,
        string $basePath,
        $allowedExtensions = [],
        $allowedMimeTypes = []
    ) {
        $this->coreFileStorageDatabase = $coreFileStorageDatabase;
        $this->directory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $this->uploaderFactory = $uploaderFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->baseTmpPath = $baseTmpPath;
        $this->basePath = $basePath;
        $this->allowedExtensions = $allowedExtensions;
        $this->allowedMimeTypes = $allowedMimeTypes;
    }

    public function setBaseTmpPath($baseTmpPath)
    {
        $this->baseTmpPath = $baseTmpPath;
    }

    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    public function setAllowedExtensions($allowedExtensions)
    {
        $this->allowedExtensions = $allowedExtensions;
    }

    public function getBaseTmpPath()
    {
        return $this->baseTmpPath;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function getAllowedExtensions()
    {
        return $this->allowedExtensions;
    }

    public function getFilePath($path, $fileName)
    {
        return rtrim($path, '/') . '/' . ltrim($fileName, '/');
    }

    public function moveFileFromTmp($fileName)
    {
        $this->createBaseDirectory();

        $baseTmpPath = $this->getBaseTmpPath();
        $basePath = $this->getBasePath();

        $baseFilePath = $this->getFilePath(
            $basePath,
            \Magento\Framework\File\Uploader::getNewFileName(
                $this->directory->getAbsolutePath(
                    $this->getFilePath($basePath, $fileName)
                )
            )
        );
        $baseTmpFilePath = $this->getFilePath($baseTmpPath, $fileName);

        try {
            $this->coreFileStorageDatabase->copyFile(
                $baseTmpFilePath,
                $baseFilePath
            );
            $this->directory->renameFile(
                $baseTmpFilePath,
                $baseFilePath
            );
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while saving the file(s).')
            );
        }

        return $fileName;
    }

    public function saveFileToTmpDir($fileId)
    {
        $this->createBaseDirectory();

        $baseTmpPath = $this->getBaseTmpPath();

        /** @var \Magento\MediaStorage\Model\File\Uploader $uploader */
        $uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
        $uploader->setAllowedExtensions($this->getAllowedExtensions());
        $uploader->setAllowRenameFiles(true);

        if (!$uploader->checkMimeType($this->allowedMimeTypes)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File validation failed. Allowed extensions: %1', implode(',', $this->allowedMimeTypes))
            );
        }

        $result = $uploader->save($this->directory->getAbsolutePath($baseTmpPath));
        unset($result['path']);

        if (!$result) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File can not be saved to the destination folder.')
            );
        }

        $result['tmp_name'] = str_replace('\\', '/', $result['tmp_name']);
        $result['url'] = $this->storeManager
                ->getStore()
                ->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . $this->getFilePath($baseTmpPath, $result['file']);
        $result['name'] = $result['file'];

        if (isset($result['file'])) {
            try {
                $relativePath = rtrim($baseTmpPath, '/') . '/' . ltrim($result['file'], '/');
                $this->coreFileStorageDatabase->saveFile($relativePath);
            } catch (\Exception $e) {
                $this->logger->critical($e);
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while saving the file(s).')
                );
            }
        }

        return $result;
    }

    protected function createBaseDirectory(): void
    {
        $this->directory->create($this->getBasePath());
        $this->directory->create($this->getBaseTmpPath());
    }
}
