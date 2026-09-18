<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\Attachment;

class AssignStores
{
    public function __construct(
        protected \MageSuite\FileAttachments\Model\ResourceModel\Attachment $attachmentResource
    ) {
    }

    public function execute(array $attachmentIds, array $storeIds, \MageSuite\FileAttachments\Model\Attachment\StoreAssignmentMode $mode): void
    {
        if (empty($attachmentIds)) {
            throw new \Magento\Framework\Exception\InputException(__('Please select at least one attachment.'));
        }

        if (empty($storeIds)) {
            throw new \Magento\Framework\Exception\InputException(__('Please select at least one store view.'));
        }

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
}
