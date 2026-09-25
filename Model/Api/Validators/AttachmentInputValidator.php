<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\Api\Validators;

class AttachmentInputValidator
{
    public function __construct(
        protected \MageSuite\FileAttachments\Model\Api\Validators\FileContentValidator $fileContentValidator
    ) {
    }

    public function validateForCreate(\MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface $attachmentData): void
    {
        if (empty($attachmentData->getName())) {
            throw \Magento\Framework\Exception\InputException::requiredField(
                \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface::NAME
            );
        }

        if ($attachmentData->getFileContent() === null) {
            throw \Magento\Framework\Exception\InputException::requiredField(
                \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface::FILE_CONTENT
            );
        }

        if ($attachmentData->getCustomerGroupIds() === null && $attachmentData->getAllCustomerGroups() !== true) {
            throw \Magento\Framework\Exception\InputException::requiredField(
                \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface::CUSTOMER_GROUP_IDS
            );
        }

        $this->validateCommonFields($attachmentData);
    }

    public function validateForUpdate(\MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface $attachmentData): void
    {
        if ($attachmentData->getName() === '') {
            throw \Magento\Framework\Exception\InputException::requiredField(
                \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface::NAME
            );
        }

        $this->validateCommonFields($attachmentData);
    }

    protected function validateCommonFields(\MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface $attachmentData): void
    {
        $this->validateNotEmptyList(
            $attachmentData->getStoreIds(),
            \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface::STORE_IDS
        );
        $this->validateNotEmptyList(
            $attachmentData->getCustomerGroupIds(),
            \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface::CUSTOMER_GROUP_IDS
        );
        $this->validateCustomerGroupsOptions($attachmentData);

        if ($attachmentData->getFileContent() === null) {
            return;
        }

        $this->fileContentValidator->validate($attachmentData->getFileContent());
    }

    protected function validateCustomerGroupsOptions(
        \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface $attachmentData
    ): void {
        if ($attachmentData->getAllCustomerGroups() !== true || $attachmentData->getCustomerGroupIds() === null) {
            return;
        }

        throw new \Magento\Framework\Exception\InputException(
            __(
                'Use either "%1" or "%2", not both.',
                \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface::CUSTOMER_GROUP_IDS,
                \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface::ALL_CUSTOMER_GROUPS
            )
        );
    }

    protected function validateNotEmptyList(?array $values, string $fieldName): void
    {
        if ($values === null || !empty($values)) {
            return;
        }

        throw new \Magento\Framework\Exception\InputException(
            __('"%1" must contain at least one value.', $fieldName)
        );
    }
}
