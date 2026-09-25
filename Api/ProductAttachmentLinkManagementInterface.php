<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Api;

interface ProductAttachmentLinkManagementInterface
{
    /**
     * @param string $sku
     * @return \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(string $sku): array;

    /**
     * @param string $sku
     * @param int[] $attachmentIds
     * @return \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function assign(string $sku, array $attachmentIds): array;

    /**
     * @param string $sku
     * @param int[] $attachmentIds
     * @return \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function replace(string $sku, array $attachmentIds): array;

    /**
     * @param string $sku
     * @param int $attachmentId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function unassign(string $sku, int $attachmentId): bool;
}
