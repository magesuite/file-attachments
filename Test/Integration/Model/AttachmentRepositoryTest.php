<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Test\Integration\Model;

class AttachmentRepositoryTest extends \PHPUnit\Framework\TestCase
{
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
        $this->assertEquals('Name', $attachment->getName());
        $this->assertEquals('magento_image.jpg', $attachment->getFilename());
        $this->assertEquals('description', $attachment->getDescription());
        $this->assertEquals('10', $attachment->getSortOrder());
    }
}
