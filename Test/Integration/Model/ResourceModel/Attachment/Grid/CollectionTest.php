<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Test\Integration\Model\ResourceModel\Attachment\Grid;

#[\Magento\TestFramework\Fixture\DbIsolation(true)]
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    public const ADMIN_STORE_ID = 0;
    public const DEFAULT_STORE_ID = 1;

    protected ?\Magento\TestFramework\ObjectManager $objectManager;

    protected ?\MageSuite\FileAttachments\Model\ResourceModel\Attachment $attachmentResource;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->attachmentResource = $this->objectManager->get(\MageSuite\FileAttachments\Model\ResourceModel\Attachment::class);
    }

    public function testItExposesAssignedStoreViewsOnEveryRow(): void
    {
        $adminAttachmentId = $this->createAttachment([self::ADMIN_STORE_ID]);
        $bothStoresAttachmentId = $this->createAttachment([self::ADMIN_STORE_ID, self::DEFAULT_STORE_ID]);

        $storeIdsByAttachmentId = $this->loadStoreIdsByAttachmentId($this->createGridCollection());

        $this->assertEquals([self::ADMIN_STORE_ID], $storeIdsByAttachmentId[$adminAttachmentId]);
        $this->assertEqualsCanonicalizing(
            [self::ADMIN_STORE_ID, self::DEFAULT_STORE_ID],
            $storeIdsByAttachmentId[$bothStoresAttachmentId]
        );
    }

    public function testItFiltersRowsByStoreViewWithoutDuplicatingThem(): void
    {
        $adminAttachmentId = $this->createAttachment([self::ADMIN_STORE_ID]);
        $bothStoresAttachmentId = $this->createAttachment([self::ADMIN_STORE_ID, self::DEFAULT_STORE_ID]);

        $collection = $this->createGridCollection();
        $collection->addFieldToFilter('store_id', ['eq' => self::DEFAULT_STORE_ID]);
        $attachmentIds = array_keys($this->loadStoreIdsByAttachmentId($collection));

        $this->assertContains($bothStoresAttachmentId, $attachmentIds);
        $this->assertNotContains($adminAttachmentId, $attachmentIds);
        $this->assertCount(1, array_keys($attachmentIds, $bothStoresAttachmentId, true));
    }

    protected function createGridCollection(): \MageSuite\FileAttachments\Model\ResourceModel\Attachment\Grid\Collection
    {
        return $this->objectManager->create(\MageSuite\FileAttachments\Model\ResourceModel\Attachment\Grid\Collection::class);
    }

    protected function loadStoreIdsByAttachmentId(\MageSuite\FileAttachments\Model\ResourceModel\Attachment\Grid\Collection $collection): array
    {
        $storeIdsByAttachmentId = [];

        foreach ($collection->getItems() as $item) {
            $storeIdsByAttachmentId[(int)$item->getData('attachment_id')] = array_map(
                'intval',
                $item->getData('store_id')
            );
        }

        return $storeIdsByAttachmentId;
    }

    protected function createAttachment(array $storeIds): int
    {
        $attachment = $this->objectManager->create(\MageSuite\FileAttachments\Api\Data\AttachmentInterface::class);
        $attachment->setFilename('magento_image.jpg');
        $attachment->setName('Name');
        $attachment->setDescription('description');
        $attachment->setSortOrder(10);
        $attachment->setData('store_ids', $storeIds);
        $this->attachmentResource->save($attachment);

        return (int)$attachment->getId();
    }
}
