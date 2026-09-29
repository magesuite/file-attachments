<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\Api;

class AttachmentManagement implements \MageSuite\FileAttachments\Api\AttachmentManagementInterface
{
    public function __construct(
        protected \MageSuite\FileAttachments\Model\Api\SaveAttachment $saveAttachment,
        protected \MageSuite\FileAttachments\Model\Api\GetAttachmentList $getAttachmentList,
        protected \MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository
    ) {
    }

    public function save(
        \MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachment,
        ?int $attachmentId = null
    ): \MageSuite\FileAttachments\Api\Data\AttachmentInterface {
        return $this->saveAttachment->save($attachment, $attachmentId);
    }

    public function deleteById(int $attachmentId): bool
    {
        return $this->attachmentRepository->deleteById($attachmentId);
    }

    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ): \MageSuite\FileAttachments\Api\Data\ApiAttachmentSearchResultsInterface {
        return $this->getAttachmentList->execute($searchCriteria);
    }
}
