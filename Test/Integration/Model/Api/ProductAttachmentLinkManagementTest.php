<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Test\Integration\Model\Api;

#[\Magento\TestFramework\Fixture\DbIsolation(true)]
class ProductAttachmentLinkManagementTest extends \PHPUnit\Framework\TestCase
{
    public const PRODUCT_SKU = 'attachment-link-product';

    protected ?\Magento\TestFramework\ObjectManager $objectManager;

    protected ?\MageSuite\FileAttachments\Api\ProductAttachmentLinkManagementInterface $productAttachmentLinkManagement;

    protected ?\Magento\TestFramework\Fixture\DataFixtureStorage $fixtures;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->productAttachmentLinkManagement = $this->objectManager->create(
            \MageSuite\FileAttachments\Api\ProductAttachmentLinkManagementInterface::class
        );
        $this->fixtures = \Magento\TestFramework\Fixture\DataFixtureStorageManager::getStorage();
    }

    #[\Magento\TestFramework\Fixture\DataFixture(\Magento\Catalog\Test\Fixture\Product::class, ['sku' => self::PRODUCT_SKU], 'product')]
    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['sort_order' => 20], 'first_attachment')]
    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['sort_order' => 10], 'second_attachment')]
    public function testItAssignsAttachmentsKeepingExistingLinks(): void
    {
        $firstAttachmentId = $this->getFixtureId('first_attachment');
        $secondAttachmentId = $this->getFixtureId('second_attachment');
        $this->productAttachmentLinkManagement->assign(self::PRODUCT_SKU, [$firstAttachmentId]);

        $apiAttachments = $this->productAttachmentLinkManagement->assign(self::PRODUCT_SKU, [$secondAttachmentId]);

        $this->assertEquals([$secondAttachmentId, $firstAttachmentId], $this->getAttachmentIds($apiAttachments));
        $this->assertEquals([self::PRODUCT_SKU], $apiAttachments[0]->getProductSkus());
    }

    #[\Magento\TestFramework\Fixture\DataFixture(\Magento\Catalog\Test\Fixture\Product::class, ['sku' => self::PRODUCT_SKU], 'product')]
    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['sort_order' => 10], 'first_attachment')]
    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['sort_order' => 20], 'second_attachment')]
    public function testItReplacesAllLinks(): void
    {
        $secondAttachmentId = $this->getFixtureId('second_attachment');
        $this->productAttachmentLinkManagement->assign(self::PRODUCT_SKU, [$this->getFixtureId('first_attachment')]);

        $apiAttachments = $this->productAttachmentLinkManagement->replace(self::PRODUCT_SKU, [$secondAttachmentId]);

        $this->assertEquals([$secondAttachmentId], $this->getAttachmentIds($apiAttachments));
    }

    #[\Magento\TestFramework\Fixture\DataFixture(\Magento\Catalog\Test\Fixture\Product::class, ['sku' => self::PRODUCT_SKU], 'product')]
    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['sort_order' => 10], 'first_attachment')]
    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['sort_order' => 20], 'second_attachment')]
    public function testItUnassignsSingleAttachment(): void
    {
        $firstAttachmentId = $this->getFixtureId('first_attachment');
        $secondAttachmentId = $this->getFixtureId('second_attachment');
        $this->productAttachmentLinkManagement->assign(self::PRODUCT_SKU, [$firstAttachmentId, $secondAttachmentId]);

        $result = $this->productAttachmentLinkManagement->unassign(self::PRODUCT_SKU, $firstAttachmentId);

        $this->assertTrue($result);
        $this->assertEquals(
            [$secondAttachmentId],
            $this->getAttachmentIds($this->productAttachmentLinkManagement->getList(self::PRODUCT_SKU))
        );
    }

    protected function getFixtureId(string $fixtureName): int
    {
        return (int)$this->fixtures->get($fixtureName)->getId();
    }

    protected function getAttachmentIds(array $apiAttachments): array
    {
        return array_map(
            fn(\MageSuite\FileAttachments\Api\Data\AttachmentInterface $apiAttachment) => $apiAttachment->getAttachmentId(),
            $apiAttachments
        );
    }
}
