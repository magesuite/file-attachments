<?php

namespace MageSuite\FileAttachments\Test\Integration\Controller\Attachment;

class DownloadTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected ?\Magento\Framework\Registry $registry;

    protected ?\MageSuite\FileAttachments\Service\HashAttachmentFilename $hashAttachmentFilename;

    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->registry = $objectManager->get(\Magento\Framework\Registry::class);
        $this->hashAttachmentFilename = $objectManager->get(\MageSuite\FileAttachments\Service\HashAttachmentFilename::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_FileAttachments::Test/Integration/_files/attachment.php
     */
    public function testItAllowDownloadFileForMatchingAttachment()
    {
        $attachmentId = $this->registry->registry('first_attachment_id');

        $fileNameHash = '34ca77f4a5f4e11480e874b1fa2dc176ce239ba1af539ddeb2f4f9c4c8b6a4b3';

        $uri = sprintf('file_attachments/attachment/download/file/%s/id/%s', $fileNameHash, $attachmentId);

        $this->dispatch($uri);

        $this->assertEquals(200, $this->getResponse()->getStatusCode());

        $this->assertEmpty($this->getMessages());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_FileAttachments::Test/Integration/_files/attachment.php
     */
    public function testAttachmentForLoggedInUserCannotBeDownloadedByUnauthorizedCustomer()
    {
        $attachmentId = $this->registry->registry('third_attachment_id');

        $fileNameHash = '34ca77f4a5f4e11480e874b1fa2dc176ce239ba1af539ddeb2f4f9c4c8b6a4b3';

        $uri = sprintf('file_attachments/attachment/download/file/%s/id/%s', $fileNameHash, $attachmentId);

        $this->dispatch($uri);

        $this->assertEquals(302, $this->getResponse()->getStatusCode());

        $messages = $this->getMessages();
        $this->assertEquals('You are not allowed to download this file.', $messages[0]);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testItDoNotAllowDownloadFileForNotExistingAttachment()
    {
        $fileNameHash = '34ca77f4a5f4e11480e874b1fa2dc176ce239ba1af539ddeb2f4f9c4c8b6a4b3';

        $uri = sprintf('file_attachments/attachment/download/file/%s/id/%s', $fileNameHash, 200);

        $this->dispatch($uri);

        $this->assertEquals(302, $this->getResponse()->getStatusCode());

        $messages = $this->getMessages();
        $this->assertEquals('Something went wrong. Please try again later.', $messages[0]);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_FileAttachments::Test/Integration/_files/attachment.php
     */
    public function testItDoNotAllowDownloadFileForWrongPostParams()
    {
        $attachmentId = $this->registry->registry('first_attachment_id');

        $fileNameHash = 'wrongHash1234';

        $uri = sprintf('file_attachments/attachment/download/file/%s/id/%s', $fileNameHash, $attachmentId);

        $this->dispatch($uri);

        $this->assertEquals(302, $this->getResponse()->getStatusCode());

        $messages = $this->getMessages();
        $this->assertEquals('Attachment does not exist.', $messages[0]);
    }

    protected function getHashFromFilename(string $filename): string
    {
        return $this->getHashFromFilename($filename);
    }
}
