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
    public function getAttachmentId(): int;

    /**
     * @param int $attachmentId
     * @return $this
     */
    public function setAttachmentId(int $attachmentId): self;

    /**
     * @return string
     */
    public function getFilename(): string;

    /**
     * @param string $filename
     * @return $this
     */
    public function setFilename(string $filename): self;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self;

    /**
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): self;

    /**
     * @return int|null
     */
    public function getSortOrder(): ?int;

    /**
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder(int $sortOrder): self;

    /**
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt): self;

    /**
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt(string $updatedAt): self;

    /**
     * @return string
     */
    public function getFilePath(): string;

    /**
     * @return string
     */
    public function getFileUrl(): string;

    /**
     * @return string
     */
    public function getDownloadUrl(): string;

    /**
     * @return string
     */
    public function getThumbnailUrl(): string;

    /**
     * @return string
     */
    public function getThumbnailPath(): string;

    /**
     * @return int[]
     */
    public function getAllowedCustomerGroupIds(): array;

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
