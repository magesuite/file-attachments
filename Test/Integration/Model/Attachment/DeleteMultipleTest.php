<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Test\Integration\Model\Attachment;

#[\Magento\TestFramework\Fixture\DbIsolation(true)]
class DeleteMultipleTest extends \PHPUnit\Framework\TestCase
{
    public const FIXTURE_FILENAME = 'magento_image.jpg';

    protected ?\Magento\TestFramework\ObjectManager $objectManager;

    protected ?\MageSuite\FileAttachments\Model\Attachment\DeleteMultiple $deleteMultiple;

    protected ?\MageSuite\FileAttachments\Model\ResourceModel\Attachment $attachmentResource;

    protected array $createdFilePaths = [];

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->attachmentResource = $this->objectManager->get(\MageSuite\FileAttachments\Model\ResourceModel\Attachment::class);
        $this->deleteMultiple = $this->objectManager->create(
            \MageSuite\FileAttachments\Model\Attachment\DeleteMultiple::class,
            ['attachmentRepository' => $this->objectManager->create(\MageSuite\FileAttachments\Model\AttachmentRepository::class)]
        );
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFilePaths as $filePath) {
            if (!file_exists($filePath)) {
                continue;
            }

            unlink($filePath);
        }

        $this->createdFilePaths = [];
    }

    public function testItDeletesEverySelectedAttachmentWithItsFile(): void
    {
        $firstAttachment = $this->createAttachment('first_attachment.jpg');
        $secondAttachment = $this->createAttachment('second_attachment.jpg');

        $result = $this->deleteMultiple->execute([$firstAttachment->getId(), $secondAttachment->getId()]);

        $this->assertEquals(
            [(int)$firstAttachment->getId(), (int)$secondAttachment->getId()],
            $result->getDeletedIds()
        );
        $this->assertEmpty($result->getFailedIds());
        $this->assertFileDoesNotExist($firstAttachment->getFilePath());
        $this->assertFileDoesNotExist($secondAttachment->getFilePath());
    }

    public function testItReportsAttachmentsThatCouldNotBeDeleted(): void
    {
        $attachment = $this->createAttachment('only_attachment.jpg');
        $missingAttachmentId = (int)$attachment->getId() + 1000;

        $result = $this->deleteMultiple->execute([$attachment->getId(), $missingAttachmentId]);

        $this->assertEquals([(int)$attachment->getId()], $result->getDeletedIds());
        $this->assertEquals([$missingAttachmentId], $result->getFailedIds());
        $this->assertFileDoesNotExist($attachment->getFilePath());
    }

    protected function createAttachment(string $filename): \MageSuite\FileAttachments\Api\Data\AttachmentInterface
    {
        $attachment = $this->objectManager->create(\MageSuite\FileAttachments\Api\Data\AttachmentInterface::class);
        $attachment->setFilename($filename);
        $attachment->setName('Name');
        $attachment->setDescription('description');
        $attachment->setSortOrder(10);
        $attachment->setData('store_ids', [\Magento\Store\Model\Store::DEFAULT_STORE_ID]);
        $this->attachmentResource->save($attachment);

        $this->createAttachmentFile($attachment);

        return $attachment;
    }

    protected function createAttachmentFile(\MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachment): void
    {
        $attachmentDirectory = dirname($attachment->getFilePath());

        if (!is_dir($attachmentDirectory)) {
            mkdir($attachmentDirectory, 0777, true);
        }

        copy(
            realpath(__DIR__ . '/../../assets') . DIRECTORY_SEPARATOR . self::FIXTURE_FILENAME,
            $attachment->getFilePath()
        );

        $this->createdFilePaths[] = $attachment->getFilePath();
    }
}
