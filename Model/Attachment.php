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

    protected \Magento\Framework\UrlInterface $urlBuilder;

    protected \MageSuite\FileAttachments\Service\HashAttachmentFilename $hashAttachmentFilename;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \MageSuite\FileAttachments\Model\FileUploader $fileUploader,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\UrlInterface $urlBuilder,
        \MageSuite\FileAttachments\Service\HashAttachmentFilename $hashAttachmentFilename,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->directoryList = $directoryList;
        $this->fileUploader = $fileUploader;
        $this->storeManager = $storeManager;
        $this->fileDriver = $fileDriver;
        $this->urlBuilder = $urlBuilder;
        $this->hashAttachmentFilename = $hashAttachmentFilename;
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

    public function getName(): ?string
    {
        return $this->_getData(self::NAME);
    }

    public function setName(string $name): self
    {
        return $this->setData(self::NAME, $name);
    }

    public function getDescription(): ?string
    {
        return $this->_getData(self::DESCRIPTION);
    }

    public function setDescription(string $description): self
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    public function getCreatedAt(): string
    {
        return (string)$this->_getData(self::CREATED_AT);
    }

    public function getSortOrder(): ?int
    {
        $sortOrder = $this->_getData(self::SORT_ORDER);

        return $sortOrder === null ? null : (int)$sortOrder;
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

        return sprintf('%s/file_attachments/attachment/thumbnail/%s', $mediaUrl, $filename);
    }

    public function getThumbnailPath(): string
    {
        $filename = $this->generateThumbnailFilename($this->getFilename());

        return $this->getUploadFolderPath(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA) . DIRECTORY_SEPARATOR . 'thumbnail' . DIRECTORY_SEPARATOR . $filename;
    }

    public function getFilePath(): string
    {
        return $this->getUploadFolderPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) . DIRECTORY_SEPARATOR . $this->getFilename();
    }

    public function getFileUrl(): string
    {
        $mediaUrl = $this->storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        return $mediaUrl . $this->fileUploader->getBasePath() . '/' . $this->getFilename();
    }

    public function getDownloadUrl(): string
    {
        $filenameHash = $this->hashAttachmentFilename->getHashFromFilename($this->getFilename());

        return $this->urlBuilder->getUrl('file_attachments/attachment/download', ['file' => $filenameHash, 'id' => $this->getId()]);
    }

    public function getAllowedCustomerGroupIds(): array
    {
        return $this->getResource()->lookupCustomerGroupIds($this->getId());
    }

    public function getStoreIds(): ?array
    {
        return $this->getIntegerList(self::STORE_IDS);
    }

    public function setStoreIds(?array $storeIds): self
    {
        return $this->setData(self::STORE_IDS, $storeIds);
    }

    public function getCustomerGroupIds(): ?array
    {
        return $this->getIntegerList(self::CUSTOMER_GROUP_IDS);
    }

    public function setCustomerGroupIds(?array $customerGroupIds): self
    {
        return $this->setData(self::CUSTOMER_GROUP_IDS, $customerGroupIds);
    }

    public function getAllCustomerGroups(): ?bool
    {
        $allCustomerGroups = $this->_getData(self::ALL_CUSTOMER_GROUPS);

        return $allCustomerGroups === null ? null : (bool)$allCustomerGroups;
    }

    public function setAllCustomerGroups(?bool $allCustomerGroups): self
    {
        return $this->setData(self::ALL_CUSTOMER_GROUPS, $allCustomerGroups);
    }

    public function getProductSkus(): ?array
    {
        if ($this->_getData(self::PRODUCT_SKUS) !== null) {
            return $this->_getData(self::PRODUCT_SKUS);
        }

        if (!$this->getId()) {
            return null;
        }

        return $this->getResource()->lookupProductSkus($this->getId());
    }

    public function setProductSkus(?array $productSkus): self
    {
        return $this->setData(self::PRODUCT_SKUS, $productSkus);
    }

    public function getFileContent(): ?\MageSuite\FileAttachments\Api\Data\FileContentInterface
    {
        return $this->_getData(self::FILE_CONTENT);
    }

    public function setFileContent(?\MageSuite\FileAttachments\Api\Data\FileContentInterface $fileContent): self
    {
        return $this->setData(self::FILE_CONTENT, $fileContent);
    }

    protected function getIntegerList(string $key): ?array
    {
        $values = $this->_getData($key);

        if ($values === null) {
            return null;
        }

        return array_values(array_map('intval', (array)$values));
    }

    protected function getUploadFolderPath(string $directoryType): string
    {
        $basePath = $this->directoryList->getPath($directoryType);
        if ($directoryType === \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) {
            return sprintf('%s/global/file_attachments/attachment', $basePath);
        }

        return sprintf('%s/file_attachments/attachment', $basePath);
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
