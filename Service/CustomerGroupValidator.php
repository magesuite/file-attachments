<?php

namespace MageSuite\FileAttachments\Service;

class CustomerGroupValidator
{
    protected \Magento\Customer\Model\Session $customerSession;
    protected \MageSuite\FileAttachments\Model\ResourceModel\Attachment $attachmentResource;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \MageSuite\FileAttachments\Model\ResourceModel\Attachment $attachmentResource
    ) {
        $this->customerSession = $customerSession;
        $this->attachmentResource = $attachmentResource;
    }

    public function isValid(\MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachment): bool
    {
        $attachmentCustomerGroups = $attachment->getAllowedCustomerGroupIds();
        $customerGroupId = $this->customerSession->getCustomerGroupId();

        return in_array($customerGroupId, $attachmentCustomerGroups);
    }
}
