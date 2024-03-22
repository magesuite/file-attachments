<?php

namespace MageSuite\FileAttachments\Service;

class HashAttachmentFilename
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

    public function getHashFromFilename(string $filename): string
    {
        return hash('sha256', $filename);
    }
}
