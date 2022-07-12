<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Model;

class Attachment extends \Magento\Framework\Model\AbstractModel implements \MageSuite\FileAttachments\Api\Data\AttachmentInterface, \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'attachment';

    //phpcs:ignore
    protected $_cacheTag = self::CACHE_TAG;

    //phpcs:ignore
    protected $_eventPrefix = self::CACHE_TAG;

    protected \Magento\Framework\App\Filesystem\DirectoryList $directoryList;

    protected \MageSuite\FileAttachments\Model\FileUploader $fileUploader;

    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    protected \Magento\Framework\Filesystem\Driver\File $fileDriver;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \MageSuite\FileAttachments\Model\FileUploader $fileUploader,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->directoryList = $directoryList;
        $this->fileUploader = $fileUploader;
        $this->storeManager = $storeManager;
        $this->fileDriver = $fileDriver;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init(\MageSuite\FileAttachments\Model\ResourceModel\Attachment::class);
    }

    public function getAttachmentId(): int
    {
        return (int)$this->_getData(self::ATTACHMENT_ID);
    }

    public function setAttachmentId(int $attachmentId): self
    {
        return $this->setData(self::ATTACHMENT_ID, $attachmentId);
    }

    public function getFilename(): string
    {
        return (string)$this->_getData(self::FILENAME);
    }

    public function setFilename(string $filename): self
    {
        return $this->setData(self::FILENAME, $filename);
    }

    public function getName(): string
    {
        return (string)$this->_getData(self::NAME);
    }

    public function setName(string $name): self
    {
        return $this->setData(self::NAME, $name);
    }

    public function getDescription(): string
    {
        return (string)$this->_getData(self::DESCRIPTION);
    }

    public function setDescription(string $description): self
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    public function getCreatedAt(): string
    {
        return (string)$this->_getData(self::CREATED_AT);
    }

    public function getSortOrder(): int
    {
        return (int)$this->_getData(self::SORT_ORDER);
    }

    public function setSortOrder(int $sortOrder): self
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    public function setCreatedAt(string $createdAt): self
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getUpdatedAt(): string
    {
        return (string)$this->_getData(self::UPDATED_AT);
    }

    public function setUpdatedAt(string $updatedAt): self
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    public function getThumbnailUrl(): string
    {
        $mediaUrl = $this->storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $filename = $this->generateThumbnailFilename($this->getFilename());

        return $mediaUrl . $this->fileUploader->getBasePath() . '/thumbnail/' . $filename;
    }

    public function getThumbnailPath(): string
    {
        $filename = $this->generateThumbnailFilename($this->getFilename());

        return $this->getUploadFolderPath() . DIRECTORY_SEPARATOR . 'thumbnail' . DIRECTORY_SEPARATOR . $filename;
    }

    public function getFilePath(): string
    {
        return $this->getUploadFolderPath() . DIRECTORY_SEPARATOR . $this->getFilename();
    }

    public function getFileUrl(): string
    {
        $mediaUrl = $this->storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $mediaUrl . $this->fileUploader->getBasePath() . '/' . $this->getFilename();
    }

    protected function getUploadFolderPath(): string
    {
        return $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)
            . DIRECTORY_SEPARATOR . 'file_attachments'
            . DIRECTORY_SEPARATOR . 'attachment';
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    protected function generateThumbnailFilename($filename): string
    {
        $filename = hash('sha256', sprintf('%s-%s', $this->getId(), $this->getFilename()));

        return $filename . '.' . \MageSuite\FileAttachments\Model\Attachment\GenerateThumbnail::IMAGE_FORMAT;
    }

    public function afterDelete()
    {
        $files = [$this->getFilePath(), $this->getThumbnailPath()];

        foreach ($files as $file) {
            if (!$this->fileDriver->isExists($file)) {
                continue;
            }

            $this->fileDriver->deleteFile($file);
        }

        return parent::afterDelete();
    }
}
