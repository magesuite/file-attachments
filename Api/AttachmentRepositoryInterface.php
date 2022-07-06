<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Api;

interface AttachmentRepositoryInterface
{
    /**
     * @param int $attchmentId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \MageSuite\FileAttachments\Api\Data\AttachmentInterface
     */
    public function getById($attchmentId);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param \MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachment
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return bool
     */
    public function save(\MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachment);

    /**
     * @param \MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachment
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return bool
     */
    public function delete(\MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachment);

    /**
     * @param int $attachmentId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return bool
     */
    public function deleteById($attachmentId);
}
