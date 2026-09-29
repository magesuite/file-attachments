<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\Api;

class SaveAttachment
{
    protected const int DEFAULT_SORT_ORDER = 0;

    public function __construct(
        protected \MageSuite\FileAttachments\Model\AttachmentFactory $attachmentFactory,
        protected \MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository,
        protected \MageSuite\FileAttachments\Model\Api\Validators\AttachmentInputValidator $attachmentInputValidator,
        protected \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupCollectionFactory,
        protected \MageSuite\FileAttachments\Model\Api\FileContentUploaderFactory $fileContentUploaderFactory,
        protected \Magento\Framework\Filesystem\Driver\File $fileDriver,
        protected \MageSuite\FileAttachments\Model\Attachment\GenerateThumbnail $generateThumbnail
    ) {
    }

    public function save(
        \MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachmentData,
        ?int $attachmentId = null
    ): \MageSuite\FileAttachments\Api\Data\AttachmentInterface {
        $attachment = $this->getAttachment($attachmentId);
        $this->validate($attachmentData, $attachmentId);
        $this->populateAttachment($attachment, $attachmentData);

        if ($attachmentData->getFileContent() === null) {
            $this->attachmentRepository->save($attachment);

            return $this->getSavedApiAttachment((int)$attachment->getId());
        }

        $this->saveWithFile($attachment, $attachmentData->getFileContent());

        return $this->getSavedApiAttachment((int)$attachment->getId());
    }

    protected function getSavedApiAttachment(int $attachmentId): \MageSuite\FileAttachments\Api\Data\AttachmentInterface
    {
        return $this->attachmentRepository->getById($attachmentId);
    }

    protected function getAttachment(?int $attachmentId): \MageSuite\FileAttachments\Model\Attachment
    {
        if ($attachmentId !== null) {
            return $this->attachmentRepository->getById($attachmentId);
        }

        $attachment = $this->attachmentFactory->create();
        $attachment->setSortOrder(self::DEFAULT_SORT_ORDER);
        $attachment->setData('store_ids', [\Magento\Store\Model\Store::DEFAULT_STORE_ID]);

        return $attachment;
    }

    protected function validate(
        \MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachmentData,
        ?int $attachmentId
    ): void {
        if ($attachmentId === null) {
            $this->attachmentInputValidator->validateForCreate($attachmentData);

            return;
        }

        $this->attachmentInputValidator->validateForUpdate($attachmentData);
    }

    protected function populateAttachment(
        \MageSuite\FileAttachments\Model\Attachment $attachment,
        \MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachmentData
    ): void {
        $fields = [
            \MageSuite\FileAttachments\Api\Data\AttachmentInterface::NAME => $attachmentData->getName(),
            \MageSuite\FileAttachments\Api\Data\AttachmentInterface::DESCRIPTION => $attachmentData->getDescription(),
            \MageSuite\FileAttachments\Api\Data\AttachmentInterface::SORT_ORDER => $attachmentData->getSortOrder(),
            \MageSuite\FileAttachments\Api\Data\AttachmentInterface::STORE_IDS => $attachmentData->getStoreIds(),
            \MageSuite\FileAttachments\Api\Data\AttachmentInterface::CUSTOMER_GROUP_IDS => $this->getCustomerGroupIds($attachmentData),
        ];

        foreach ($fields as $key => $value) {
            if ($value === null) {
                continue;
            }

            $attachment->setData($key, $value);
        }
    }

    protected function getCustomerGroupIds(\MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachmentData): ?array
    {
        if ($attachmentData->getAllCustomerGroups() === true) {
            return array_map('intval', $this->customerGroupCollectionFactory->create()->getAllIds());
        }

        return $attachmentData->getCustomerGroupIds();
    }

    protected function saveWithFile(
        \MageSuite\FileAttachments\Model\Attachment $attachment,
        \MageSuite\FileAttachments\Api\Data\FileContentInterface $fileContent
    ): void {
        $previousFilePaths = $this->getPreviousFilePaths($attachment);
        $attachment->setFilename($this->fileContentUploaderFactory->create()->upload($fileContent));

        try {
            $this->attachmentRepository->save($attachment);
        } catch (\Magento\Framework\Exception\CouldNotSaveException $exception) {
            $this->deleteFiles([$attachment->getFilePath()]);

            throw $exception;
        }

        $this->generateThumbnail->execute($attachment);
        $this->deleteFiles(array_diff(
            $previousFilePaths,
            [$attachment->getFilePath(), $attachment->getThumbnailPath()]
        ));
    }

    protected function deleteFiles(array $filePaths): void
    {
        foreach ($filePaths as $filePath) {
            if (!$this->fileDriver->isExists($filePath)) {
                continue;
            }

            $this->fileDriver->deleteFile($filePath);
        }
    }

    protected function getPreviousFilePaths(\MageSuite\FileAttachments\Model\Attachment $attachment): array
    {
        if (!$attachment->getId()) {
            return [];
        }

        return [$attachment->getFilePath(), $attachment->getThumbnailPath()];
    }
}
