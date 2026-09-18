<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\Attachment;

class DeleteMultiple
{
    public function __construct(
        protected \MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository,
        protected \Magento\Framework\DataObjectFactory $dataObjectFactory,
        protected \Psr\Log\LoggerInterface $logger
    ) {
    }

    public function execute(array $attachmentIds): \Magento\Framework\DataObject
    {
        $deletedIds = [];
        $failedIds = [];

        foreach ($attachmentIds as $attachmentId) {
            $attachmentId = (int)$attachmentId;

            if ($this->deleteAttachment($attachmentId)) {
                $deletedIds[] = $attachmentId;

                continue;
            }

            $failedIds[] = $attachmentId;
        }

        return $this->dataObjectFactory->create([
            'data' => [
                'deleted_ids' => $deletedIds,
                'failed_ids' => $failedIds,
            ]
        ]);
    }

    protected function deleteAttachment(int $attachmentId): bool
    {
        try {
            $this->attachmentRepository->deleteById($attachmentId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException | \Magento\Framework\Exception\CouldNotSaveException $exception) {
            $this->logger->error($exception);

            return false;
        }

        return true;
    }
}
