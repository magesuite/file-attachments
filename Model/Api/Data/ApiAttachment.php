<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\Api\Data;

class ApiAttachment extends \Magento\Framework\DataObject implements \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface
{
    public function getAttachmentId(): ?int
    {
        $value = $this->_getData(self::ATTACHMENT_ID);

        return $value === null ? null : (int)$value;
    }

    public function setAttachmentId(?int $attachmentId): self
    {
        return $this->setData(self::ATTACHMENT_ID, $attachmentId);
    }

    public function getName(): ?string
    {
        return $this->_getData(self::NAME);
    }

    public function setName(?string $name): self
    {
        return $this->setData(self::NAME, $name);
    }

    public function getDescription(): ?string
    {
        return $this->_getData(self::DESCRIPTION);
    }

    public function setDescription(?string $description): self
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    public function getSortOrder(): ?int
    {
        $value = $this->_getData(self::SORT_ORDER);

        return $value === null ? null : (int)$value;
    }

    public function setSortOrder(?int $sortOrder): self
    {
        return $this->setData(self::SORT_ORDER, $sortOrder);
    }

    public function getFilename(): ?string
    {
        return $this->_getData(self::FILENAME);
    }

    public function setFilename(?string $filename): self
    {
        return $this->setData(self::FILENAME, $filename);
    }

    public function getStoreIds(): ?array
    {
        return $this->_getData(self::STORE_IDS);
    }

    public function setStoreIds(?array $storeIds): self
    {
        return $this->setData(self::STORE_IDS, $storeIds);
    }

    public function getCustomerGroupIds(): ?array
    {
        return $this->_getData(self::CUSTOMER_GROUP_IDS);
    }

    public function setCustomerGroupIds(?array $customerGroupIds): self
    {
        return $this->setData(self::CUSTOMER_GROUP_IDS, $customerGroupIds);
    }

    public function getAllCustomerGroups(): ?bool
    {
        $value = $this->_getData(self::ALL_CUSTOMER_GROUPS);

        return $value === null ? null : (bool)$value;
    }

    public function setAllCustomerGroups(?bool $allCustomerGroups): self
    {
        return $this->setData(self::ALL_CUSTOMER_GROUPS, $allCustomerGroups);
    }

    public function getProductSkus(): ?array
    {
        return $this->_getData(self::PRODUCT_SKUS);
    }

    public function setProductSkus(?array $productSkus): self
    {
        return $this->setData(self::PRODUCT_SKUS, $productSkus);
    }

    public function getDownloadUrl(): ?string
    {
        return $this->_getData(self::DOWNLOAD_URL);
    }

    public function setDownloadUrl(?string $downloadUrl): self
    {
        return $this->setData(self::DOWNLOAD_URL, $downloadUrl);
    }

    public function getThumbnailUrl(): ?string
    {
        return $this->_getData(self::THUMBNAIL_URL);
    }

    public function setThumbnailUrl(?string $thumbnailUrl): self
    {
        return $this->setData(self::THUMBNAIL_URL, $thumbnailUrl);
    }

    public function getCreatedAt(): ?string
    {
        return $this->_getData(self::CREATED_AT);
    }

    public function setCreatedAt(?string $createdAt): self
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    public function getUpdatedAt(): ?string
    {
        return $this->_getData(self::UPDATED_AT);
    }

    public function setUpdatedAt(?string $updatedAt): self
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    public function getFileContent(): ?\MageSuite\FileAttachments\Api\Data\FileContentInterface
    {
        return $this->_getData(self::FILE_CONTENT);
    }

    public function setFileContent(?\MageSuite\FileAttachments\Api\Data\FileContentInterface $fileContent): self
    {
        return $this->setData(self::FILE_CONTENT, $fileContent);
    }
}
