<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Observer;

class SaveProductAttachments implements \Magento\Framework\Event\ObserverInterface
{
    protected \Magento\Framework\Serialize\SerializerInterface $serializer;

    protected \MageSuite\FileAttachments\Model\ResourceModel\Attachment $attachmentResource;

    public function __construct(
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \MageSuite\FileAttachments\Model\ResourceModel\Attachment $attachmentResource
    ) {
        $this->serializer = $serializer;
        $this->attachmentResource = $attachmentResource;
    }

    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        $controller = $observer->getController();
        $product = $observer->getProduct();
        $fileAttachments = $controller->getRequest()->getPostValue('file_attachments');

        if ($fileAttachments === null) {
            return;
        }

        try {
            $fileAttachments = $this->serializer->unserialize($fileAttachments);
        } catch (\InvalidArgumentException $e) {
            $fileAttachments = [];
        }

        $this->attachmentResource->assignAttachmentsToProduct((int)$product->getId(), array_keys($fileAttachments));
    }
}
