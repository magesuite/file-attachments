<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Test\Integration\Model\Attachment;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class GenerateThumbnailTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\FileAttachments\Model\Attachment\GenerateThumbnail $generateThumbnail;

    protected ?\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository;

    protected ?\Magento\Framework\Registry $registry;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->generateThumbnail = $objectManager->get(\MageSuite\FileAttachments\Model\Attachment\GenerateThumbnail::class);
        $this->attachmentRepository = $objectManager->get(\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface::class);
        $this->registry = $objectManager->get(\Magento\Framework\Registry::class);
    }

    /**
     * @magentoDataFixture MageSuite_FileAttachments::Test/Integration/_files/attachment.php
     */
    public function testItGeneratesProperThumbnailImage(): void
    {
        $expectedThumbnailPath = realpath(__DIR__ . '/../../assets') . '/magento_image_thumbnail.jpg';
        $expectedImagePath = realpath(__DIR__ . '/../../assets') . '/magento_image.jpg';
        $ImagePath = realpath(__DIR__ . '/../../assets') . '/magento_image.jpg';

        $attachmentId = $this->registry->registry('attachmentId');
        $attachment = $this->attachmentRepository->getById($attachmentId);

        $this->generateThumbnail->execute($attachment);

        $this->assertEquals(
            getimagesize($expectedThumbnailPath),
            getimagesize($attachment->getThumbnailPath())
        );

        $this->assertLessThan(filesize($expectedImagePath), filesize($attachment->getThumbnailPath()));
    }
}
