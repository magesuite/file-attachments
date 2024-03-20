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

    public function isValid(int|string $attachmentId): bool
    {
        $attachmentCustomerGroups = $this->attachmentResource->lookupCustomerGroupIds($attachmentId);
        $customerGroupId = $this->customerSession->getCustomerGroupId();

        return in_array($customerGroupId, $attachmentCustomerGroups);
    }
}
