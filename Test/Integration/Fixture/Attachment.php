<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Test\Integration\Fixture;

class Attachment implements \Magento\TestFramework\Fixture\RevertibleDataFixtureInterface
{
    protected const DEFAULT_DATA = [
        'filename' => 'attachment_%uniqid%.jpg',
        'name' => 'Attachment %uniqid%',
        'description' => 'description',
        'sort_order' => 0,
        'store_ids' => [\Magento\Store\Model\Store::DEFAULT_STORE_ID],
        'customer_group_ids' => [0],
    ];

    public function __construct(
        protected \MageSuite\FileAttachments\Model\AttachmentFactory $attachmentFactory,
        protected \MageSuite\FileAttachments\Model\ResourceModel\Attachment $attachmentResource,
        protected \Magento\TestFramework\Fixture\Data\ProcessorInterface $dataProcessor
    ) {
    }

    public function apply(array $data = []): ?\Magento\Framework\DataObject
    {
        $attachment = $this->attachmentFactory->create();
        $attachment->setData($this->dataProcessor->process($this, array_replace(self::DEFAULT_DATA, $data)));
        $this->attachmentResource->save($attachment);

        return $attachment;
    }

    public function revert(\Magento\Framework\DataObject $data): void
    {
        $attachment = $this->attachmentFactory->create();
        $this->attachmentResource->load($attachment, $data->getId());

        if (!$attachment->getId()) {
            return;
        }

        $this->attachmentResource->delete($attachment);
    }
}
