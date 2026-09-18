<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\Attachment;

class AssignStores
{
    public function __construct(
        protected \MageSuite\FileAttachments\Model\ResourceModel\Attachment $attachmentResource,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
    }

    public function execute(array $attachmentIds, array $storeIds, \MageSuite\FileAttachments\Model\Attachment\StoreAssignmentMode $mode): void
    {
        if (empty($attachmentIds)) {
            throw new \Magento\Framework\Exception\InputException(__('Please select at least one attachment.'));
        }

        $storeIds = $this->resolveStoreIds($storeIds);
        $connection = $this->attachmentResource->getConnection();

        try {
            $connection->beginTransaction();
            $this->attachmentResource->assignStoresToAttachments(
                $attachmentIds,
                $storeIds,
                $mode === \MageSuite\FileAttachments\Model\Attachment\StoreAssignmentMode::REPLACE
            );
            $connection->commit();
        } catch (\Exception $exception) {
            $connection->rollBack();

            throw $exception;
        }
    }

    protected function resolveStoreIds(array $storeIds): array
    {
        if (!empty($storeIds)) {
            return $storeIds;
        }

        if ($this->storeManager->isSingleStoreMode()) {
            return [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
        }

        throw new \Magento\Framework\Exception\InputException(__('Please select at least one store view.'));
    }
}
