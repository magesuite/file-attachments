<?php

namespace MageSuite\FileAttachments\Test\Integration\Controller\Attachment;

class DownloadTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected ?\MageSuite\FileAttachments\ViewModel\Product\Attachment $attachmentViewModel;

    protected ?\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository;

    protected ?\Magento\Framework\Registry $registry;

    protected ?\Magento\Customer\Model\Customer $customer;

    protected ?\Magento\Customer\Model\Session $customerSession;

    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->attachmentViewModel = $objectManager->get(\MageSuite\FileAttachments\ViewModel\Product\Attachment::class);
        $this->attachmentRepository = $objectManager->get(\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface::class);
        $this->registry = $objectManager->get(\Magento\Framework\Registry::class);
        $this->customer = $objectManager->create(\Magento\Customer\Model\Customer::class);
        $this->customerSession = $objectManager->get(\Magento\Customer\Model\Session::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_FileAttachments::Test/Integration/_files/attachment.php
     */
    public function testItAllowDownloadFileForMatchingAttachment()
    {
        $attachmentId = $this->registry->registry('first_attachment_id');
        $postData = [
            'file' => 'magento_image.jpg',
            'id' => $attachmentId
        ];
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('file_attachments/attachment/download');

        $this->assertEquals(200, $this->getResponse()->getStatusCode());

        $this->assertEmpty($this->getMessages());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_FileAttachments::Test/Integration/_files/attachment.php
     */
    public function testItDoNotAllowDownloadFileForNotMatchingAttachment()
    {
        $attachmentId = $this->registry->registry('third_attachment_id');
        $postData = [
            'file' => 'magento_image.jpg',
            'id' => $attachmentId
        ];
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('file_attachments/attachment/download');

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
        $postData = [
            'file' => 'magento_image.jpg',
            'id' => 200
        ];
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('file_attachments/attachment/download');

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
        $postData = [
            'file' => 'magento_image_wrong_filename.jpg',
            'id' => $attachmentId
        ];
        $this->getRequest()->setPostValue($postData);
        $this->dispatch('file_attachments/attachment/download');

        $this->assertEquals(302, $this->getResponse()->getStatusCode());

        $messages = $this->getMessages();
        $this->assertEquals('Attachment does not exist.', $messages[0]);
    }
}
