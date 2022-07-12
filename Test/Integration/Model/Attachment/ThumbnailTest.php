<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Test\Integration\Model\Attachment;

class ThumbnailTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \MageSuite\FileAttachments\Model\Attachment\Thumbnail
     */
    protected $thumbnail;

    /**
     * @var \MageSuite\FileAttachments\Api\AttachmentRepositoryInterface
     */
    protected $attachmentRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->thumbnail = $objectManager->get(\MageSuite\FileAttachments\Model\Attachment\Thumbnail::class);
        $this->attachmentRepository = $objectManager->get(\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface::class);
        $this->registry = $objectManager->get(\Magento\Framework\Registry::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture MageSuite_FileAttachments::Test/Integration/_files/attachment.php
     */
    public function testItGeneratesProperThumbnailImage(): void
    {
        $attachmentId = $this->registry->registry('attachmentId');
        $attachment = $this->attachmentRepository->getById($attachmentId);
        $this->thumbnail->execute($attachment);
        $sourceImagePath = realpath(__DIR__ . '/../../assets') . '/magento_image_thumbnail.jpg';
        $this->assertFileEquals($attachment->getThumbnailPath(), $sourceImagePath);
    }
}
