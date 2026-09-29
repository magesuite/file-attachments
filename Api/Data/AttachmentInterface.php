<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Api\Data;

interface AttachmentInterface
{
    public const ATTACHMENT_ID = 'attachment_id';
    public const FILENAME = 'filename';
    public const NAME = 'name';
    public const DESCRIPTION = 'description';
    public const SORT_ORDER = 'sort_order';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const STORE_IDS = 'store_ids';
    public const CUSTOMER_GROUP_IDS = 'customer_group_ids';
    public const ALL_CUSTOMER_GROUPS = 'all_customer_groups';
    public const PRODUCT_SKUS = 'product_skus';
    public const FILE_CONTENT = 'file_content';

    /**
     * @return int
     */
    public function getAttachmentId();

    /**
     * @param int $attachmentId
     * @return AttachmentInterface
     */
    public function setAttachmentId(int $attachmentId);

    /**
     * @return string
     */
    public function getFilename();

    /**
     * @param string $filename
     * @return AttachmentInterface
     */
    public function setFilename(string $filename);

    /**
     * @return string|null
     */
    public function getName();

    /**
     * @param string $name
     * @return AttachmentInterface
     */
    public function setName(string $name);

    /**
     * @return string|null
     */
    public function getDescription();

    /**
     * @param string $description
     * @return AttachmentInterface
     */
    public function setDescription(string $description);

    /**
     * @return int|null
     */
    public function getSortOrder();

    /**
     * @param int $sortOrder
     * @return AttachmentInterface
     */
    public function setSortOrder(int $sortOrder);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return AttachmentInterface
     */
    public function setCreatedAt(string $createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     * @return AttachmentInterface
     */
    public function setUpdatedAt(string $updatedAt);

    /**
     * @return string
     */
    public function getFilePath();

    /**
     * @return string
     */
    public function getFileUrl();

    /**
     * @return string
     */
    public function getDownloadUrl();

    /**
     * @return string
     */
    public function getThumbnailUrl();

    /**
     * @return string
     */
    public function getThumbnailPath();

    /**
     * @return int[]
     */
    public function getAllowedCustomerGroupIds();

    /**
     * @return int[]|null
     */
    public function getStoreIds(): ?array;

    /**
     * @param int[]|null $storeIds
     * @return $this
     */
    public function setStoreIds(?array $storeIds): self;

    /**
     * @return int[]|null
     */
    public function getCustomerGroupIds(): ?array;

    /**
     * @param int[]|null $customerGroupIds
     * @return $this
     */
    public function setCustomerGroupIds(?array $customerGroupIds): self;

    /**
     * @return bool|null
     */
    public function getAllCustomerGroups(): ?bool;

    /**
     * @param bool|null $allCustomerGroups
     * @return $this
     */
    public function setAllCustomerGroups(?bool $allCustomerGroups): self;

    /**
     * @return string[]|null
     */
    public function getProductSkus(): ?array;

    /**
     * @param string[]|null $productSkus
     * @return $this
     */
    public function setProductSkus(?array $productSkus): self;

    /**
     * @return \MageSuite\FileAttachments\Api\Data\FileContentInterface|null
     */
    public function getFileContent(): ?\MageSuite\FileAttachments\Api\Data\FileContentInterface;

    /**
     * @param \MageSuite\FileAttachments\Api\Data\FileContentInterface|null $fileContent
     * @return $this
     */
    public function setFileContent(?\MageSuite\FileAttachments\Api\Data\FileContentInterface $fileContent): self;
}
