<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Test\Integration\ViewModel\Product;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class AttachmentTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\FileAttachments\ViewModel\Product\Attachment $attachment;

    protected ?\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository;

    protected ?\Magento\Framework\Registry $registry;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->attachment = $objectManager->get(\MageSuite\FileAttachments\ViewModel\Product\Attachment::class);
        $this->attachmentRepository = $objectManager->get(\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface::class);
        $this->registry = $objectManager->get(\Magento\Framework\Registry::class);
    }

    /**
     * @magentoDataFixture MageSuite_FileAttachments::Test/Integration/_files/attachment.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_FileAttachments::Test/Integration/_files/product_attachment.php
     */
    public function testItReturnCorrectListOfAttachmentsForCustomer(): void
    {
        $attachmentList = $this->attachment->getAttachments();

        $this->assertCount(1, $attachmentList);

        $attachment = array_shift($attachmentList);

        $attachment = $this->attachmentRepository->getById($attachment->getId());

        $this->assertEquals('first_attachment', $attachment->getName());
        $this->assertEquals('magento_image.jpg', $attachment->getFilename());
        $this->assertEquals('first_attachment_description', $attachment->getDescription());
        $this->assertEquals('10', $attachment->getSortOrder());
    }
}
