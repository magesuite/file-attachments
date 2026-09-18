<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Test\Integration\Model\Attachment;

#[\Magento\TestFramework\Fixture\DbIsolation(true)]
class AssignStoresTest extends \PHPUnit\Framework\TestCase
{
    public const ADMIN_STORE_ID = 0;
    public const DEFAULT_STORE_ID = 1;

    protected ?\Magento\TestFramework\ObjectManager $objectManager;

    protected ?\MageSuite\FileAttachments\Model\Attachment\AssignStores $assignStores;

    protected ?\MageSuite\FileAttachments\Model\ResourceModel\Attachment $attachmentResource;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->assignStores = $this->objectManager->create(\MageSuite\FileAttachments\Model\Attachment\AssignStores::class);
        $this->attachmentResource = $this->objectManager->get(\MageSuite\FileAttachments\Model\ResourceModel\Attachment::class);
    }

    public function testItAddsStoreViewsKeepingExistingAssignments(): void
    {
        $attachmentId = $this->createAttachment([self::DEFAULT_STORE_ID]);

        $this->assignStores->execute(
            [$attachmentId],
            [self::ADMIN_STORE_ID],
            \MageSuite\FileAttachments\Model\Attachment\StoreAssignmentMode::ADD
        );

        $this->assertEqualsCanonicalizing(
            [self::ADMIN_STORE_ID, self::DEFAULT_STORE_ID],
            $this->getAssignedStoreIds($attachmentId)
        );
    }

    public function testItDoesNotDuplicateAlreadyAssignedStoreViews(): void
    {
        $attachmentId = $this->createAttachment([self::DEFAULT_STORE_ID]);

        $this->assignStores->execute(
            [$attachmentId],
            [self::ADMIN_STORE_ID, self::DEFAULT_STORE_ID],
            \MageSuite\FileAttachments\Model\Attachment\StoreAssignmentMode::ADD
        );

        $this->assertEqualsCanonicalizing(
            [self::ADMIN_STORE_ID, self::DEFAULT_STORE_ID],
            $this->getAssignedStoreIds($attachmentId)
        );
    }

    public function testItReplacesExistingStoreViewAssignments(): void
    {
        $attachmentId = $this->createAttachment([self::ADMIN_STORE_ID, self::DEFAULT_STORE_ID]);

        $this->assignStores->execute(
            [$attachmentId],
            [self::DEFAULT_STORE_ID],
            \MageSuite\FileAttachments\Model\Attachment\StoreAssignmentMode::REPLACE
        );

        $this->assertEquals([self::DEFAULT_STORE_ID], $this->getAssignedStoreIds($attachmentId));
    }

    public function testItAssignsEverySelectedAttachment(): void
    {
        $firstAttachmentId = $this->createAttachment([self::DEFAULT_STORE_ID]);
        $secondAttachmentId = $this->createAttachment([self::DEFAULT_STORE_ID]);

        $this->assignStores->execute(
            [$firstAttachmentId, $secondAttachmentId],
            [self::ADMIN_STORE_ID],
            \MageSuite\FileAttachments\Model\Attachment\StoreAssignmentMode::REPLACE
        );

        $this->assertEquals([self::ADMIN_STORE_ID], $this->getAssignedStoreIds($firstAttachmentId));
        $this->assertEquals([self::ADMIN_STORE_ID], $this->getAssignedStoreIds($secondAttachmentId));
    }

    public function testItRejectsEmptyStoreViewSelection(): void
    {
        $attachmentId = $this->createAttachment([self::DEFAULT_STORE_ID]);

        $this->expectException(\Magento\Framework\Exception\InputException::class);

        $this->assignStores->execute(
            [$attachmentId],
            [],
            \MageSuite\FileAttachments\Model\Attachment\StoreAssignmentMode::REPLACE
        );
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

    protected function getAssignedStoreIds(int $attachmentId): array
    {
        return array_map('intval', $this->attachmentResource->lookupStoreIds($attachmentId));
    }
}
