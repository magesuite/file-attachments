<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Test\Integration\Model;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class AttachmentRepositoryTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository;

    protected ?\Magento\Framework\Registry $registry;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->attachmentRepository = $objectManager->get(\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface::class);
        $this->registry = $objectManager->get(\Magento\Framework\Registry::class);
    }

    /**
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
