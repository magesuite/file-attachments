<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\Api;

class FileContentUploader extends \Magento\MediaStorage\Model\File\Uploader
{
    protected const TMP_FILE_PREFIX = 'file_attachments_api';

    protected \Magento\Framework\Filesystem\Directory\WriteInterface $attachmentDirectory;

    protected \Magento\Framework\Filesystem\Directory\WriteInterface $systemTmpDirectory;

    public function __construct(
        \Magento\MediaStorage\Helper\File\Storage\Database $coreFileStorageDb,
        \Magento\MediaStorage\Helper\File\Storage $coreFileStorage,
        \Magento\MediaStorage\Model\File\Validator\NotProtectedExtension $validator,
        \Magento\Framework\Filesystem $filesystem,
        protected \Magento\Framework\File\Mime $fileMimeResolver,
        protected \MageSuite\FileAttachments\Model\FileUploader $fileUploader
    ) {
        $this->_coreFileStorageDb = $coreFileStorageDb;
        $this->_coreFileStorage = $coreFileStorage;
        $this->_validator = $validator;
        $this->attachmentDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $this->systemTmpDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::SYS_TMP);
    }

    public function upload(\MageSuite\FileAttachments\Api\Data\FileContentInterface $fileContent): string
    {
        $tmpFileName = uniqid(self::TMP_FILE_PREFIX, true);
        $fileSize = $this->systemTmpDirectory->writeFile(
            $tmpFileName,
            (string)base64_decode($fileContent->getFileData(), true)
        );

        try {
            return $this->saveTmpFile($tmpFileName, $fileContent->getName(), $fileSize);
        } finally {
            $this->removeTmpFile($tmpFileName);
        }
    }

    protected function saveTmpFile(string $tmpFileName, string $name, int $fileSize): string
    {
        $tmpFilePath = $this->systemTmpDirectory->getAbsolutePath($tmpFileName);
        $mimeType = $this->fileMimeResolver->getMimeType($tmpFilePath);

        $this->_file = [
            'name' => $name,
            'type' => $mimeType,
            'tmp_name' => $tmpFilePath,
            'error' => 0,
            'size' => $fileSize,
        ];
        $this->_fileExists = true;
        $this->_uploadType = self::SINGLE_STYLE;
        $this->setAllowRenameFiles(true);
        $this->setAllowCreateFolders(true);
        $this->setFilesDispersion(false);
        $this->setAllowedExtensions($this->fileUploader->getAllowedExtensions());

        $this->validateFileType($mimeType);

        $result = $this->save($this->attachmentDirectory->getAbsolutePath($this->fileUploader->getBasePath()));

        if (empty($result['file'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('File can not be saved to the destination folder.')
            );
        }

        return $result['file'];
    }

    protected function validateFileType(string $mimeType): void
    {
        if (!$this->checkAllowedExtension($this->getFileExtension())) {
            throw new \Magento\Framework\Exception\InputException(
                __(
                    'File extension is not allowed. Allowed extensions: %1',
                    implode(', ', $this->fileUploader->getAllowedExtensions())
                )
            );
        }

        $allowedMimeTypes = $this->fileUploader->getAllowedMimeTypes();

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            throw new \Magento\Framework\Exception\InputException(
                __('File type is not allowed. Allowed types: %1', implode(', ', $allowedMimeTypes))
            );
        }
    }

    protected function removeTmpFile(string $tmpFileName): void
    {
        if (!$this->systemTmpDirectory->isExist($tmpFileName)) {
            return;
        }

        $this->systemTmpDirectory->delete($tmpFileName);
    }
}
