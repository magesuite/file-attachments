<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\Api;

class ProductAttachmentLinkManagement implements \MageSuite\FileAttachments\Api\ProductAttachmentLinkManagementInterface
{
    public function __construct(
        protected \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        protected \MageSuite\FileAttachments\Model\ResourceModel\Attachment $attachmentResource,
        protected \MageSuite\FileAttachments\Model\ResourceModel\Attachment\CollectionFactory $attachmentCollectionFactory,
        protected \Magento\Framework\Indexer\CacheContext $cacheContext,
        protected \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
    }

    public function getList(string $sku): array
    {
        return $this->getProductAttachments($this->getProductId($sku));
    }

    public function assign(string $sku, array $attachmentIds): array
    {
        $productId = $this->getProductId($sku);
        $attachmentIds = $this->validateAttachmentIds($attachmentIds);

        $this->saveLinks($productId, array_merge($this->getLinkedAttachmentIds($productId), $attachmentIds));

        return $this->getProductAttachments($productId);
    }

    public function replace(string $sku, array $attachmentIds): array
    {
        $productId = $this->getProductId($sku);
        $attachmentIds = $this->validateAttachmentIds($attachmentIds);

        $this->saveLinks($productId, $attachmentIds);

        return $this->getProductAttachments($productId);
    }

    public function unassign(string $sku, int $attachmentId): bool
    {
        $productId = $this->getProductId($sku);
        $linkedAttachmentIds = $this->getLinkedAttachmentIds($productId);

        if (!in_array($attachmentId, $linkedAttachmentIds, true)) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The file attachment with the "%1" ID is not assigned to the product "%2".', $attachmentId, $sku)
            );
        }

        $this->saveLinks($productId, array_diff($linkedAttachmentIds, [$attachmentId]));

        return true;
    }

    protected function getProductId(string $sku): int
    {
        return (int)$this->productRepository->get($sku)->getId();
    }

    protected function getLinkedAttachmentIds(int $productId): array
    {
        return array_map('intval', $this->attachmentResource->getProductAttachmentIds($productId));
    }

    protected function validateAttachmentIds(array $attachmentIds): array
    {
        $attachmentIds = array_values(array_unique(array_map('intval', $attachmentIds)));

        if (empty($attachmentIds)) {
            return [];
        }

        $attachmentCollection = $this->attachmentCollectionFactory->create();
        $attachmentCollection->addFieldToFilter('attachment_id', ['in' => $attachmentIds]);
        $missingAttachmentIds = array_diff($attachmentIds, array_map('intval', $attachmentCollection->getAllIds()));

        if (empty($missingAttachmentIds)) {
            return $attachmentIds;
        }

        throw new \Magento\Framework\Exception\NoSuchEntityException(
            __('File attachments with the following IDs do not exist: %1', implode(', ', $missingAttachmentIds))
        );
    }

    protected function saveLinks(int $productId, array $attachmentIds): void
    {
        $this->attachmentResource->assignAttachmentsToProduct($productId, array_values(array_unique($attachmentIds)));

        $this->cacheContext->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, [$productId]);
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $this->cacheContext]);
    }

    protected function getProductAttachments(int $productId): array
    {
        $attachmentIds = $this->getLinkedAttachmentIds($productId);

        if (empty($attachmentIds)) {
            return [];
        }

        $attachmentCollection = $this->attachmentCollectionFactory->create();
        $attachmentCollection->addFieldToFilter('attachment_id', ['in' => $attachmentIds]);
        $attachmentCollection->sortByOrder();
        $attachmentCollection->addStoreIdsToResults();
        $attachmentCollection->addCustomerGroupIdsToResults();
        $attachmentCollection->addProductSkusToResults();

        return array_values($attachmentCollection->getItems());
    }
}
