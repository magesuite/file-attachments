<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\Api;

class AttachmentMapper
{
    public function __construct(
        protected \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterfaceFactory $apiAttachmentFactory,
        protected \MageSuite\FileAttachments\Model\ResourceModel\Attachment $attachmentResource
    ) {
    }

    public function toApiAttachment(
        \MageSuite\FileAttachments\Model\Attachment $attachment
    ): \MageSuite\FileAttachments\Api\Data\ApiAttachmentInterface {
        $apiAttachment = $this->apiAttachmentFactory->create();
        $apiAttachment->setAttachmentId($attachment->getAttachmentId());
        $apiAttachment->setName($attachment->getName());
        $apiAttachment->setDescription($attachment->getDescription());
        $apiAttachment->setSortOrder($attachment->getSortOrder());
        $apiAttachment->setFilename($attachment->getFilename());
        $apiAttachment->setStoreIds($this->getIntegerList($attachment->getData('store_ids')));
        $apiAttachment->setCustomerGroupIds($this->getIntegerList($attachment->getData('customer_group_ids')));
        $apiAttachment->setProductSkus($this->getProductSkus($attachment));
        $apiAttachment->setDownloadUrl($attachment->getDownloadUrl());
        $apiAttachment->setThumbnailUrl($attachment->getThumbnailUrl());
        $apiAttachment->setCreatedAt($attachment->getCreatedAt());
        $apiAttachment->setUpdatedAt($attachment->getUpdatedAt());

        return $apiAttachment;
    }

    protected function getProductSkus(\MageSuite\FileAttachments\Model\Attachment $attachment): array
    {
        return $attachment->getData('product_skus')
            ?? $this->attachmentResource->lookupProductSkus($attachment->getId());
    }

    protected function getIntegerList(mixed $values): array
    {
        return array_values(array_map('intval', (array)$values));
    }
}
