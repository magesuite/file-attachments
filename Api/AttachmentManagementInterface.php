<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Api;

interface AttachmentManagementInterface
{
    /**
     * @param \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface $attachment
     * @param int|null $attachmentId
     * @return \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface $attachment,
        ?int $attachmentId = null
    ): \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface;

    /**
     * @param int $attachmentId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function deleteById(int $attachmentId): bool;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \MageSuite\FileAttachments\Api\Data\ApiAttachmentSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ): \MageSuite\FileAttachments\Api\Data\ApiAttachmentSearchResultsInterface;
}
