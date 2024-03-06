<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Test\Integration\ViewModel\Product;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class AttachmentTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\FileAttachments\ViewModel\Product\Attachment $attachmentViewModel;

    protected ?\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository;

    protected ?\Magento\Framework\Registry $registry;

    protected ?\Magento\Customer\Model\Customer $customer;

    protected ?\Magento\Customer\Model\Session $customerSession;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->attachmentViewModel = $objectManager->get(\MageSuite\FileAttachments\ViewModel\Product\Attachment::class);
        $this->attachmentRepository = $objectManager->get(\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface::class);
        $this->registry = $objectManager->get(\Magento\Framework\Registry::class);
        $this->customer = $objectManager->create(\Magento\Customer\Model\Customer::class);
        $this->customerSession = $objectManager->get(\Magento\Customer\Model\Session::class);
    }

    /**
     * @magentoDataFixture MageSuite_FileAttachments::Test/Integration/_files/attachment.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_FileAttachments::Test/Integration/_files/product_attachment.php
     */
    public function testItReturnCorrectListOfAttachmentsForGuest(): void
    {
        $attachmentList = $this->attachmentViewModel->getAttachments();

        $this->assertCount(1, $attachmentList);

        $attachment = array_shift($attachmentList);

        $attachment = $this->attachmentRepository->getById($attachment->getId());

        $this->assertEquals('first_attachment', $attachment->getName());
        $this->assertEquals('magento_image.jpg', $attachment->getFilename());
        $this->assertEquals('first_attachment_description', $attachment->getDescription());
        $this->assertEquals('10', $attachment->getSortOrder());
    }

    /**
     * @magentoDataFixture MageSuite_FileAttachments::Test/Integration/_files/attachment.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture MageSuite_FileAttachments::Test/Integration/_files/product_attachment.php
     */
    public function testItReturnCorrectListOfAttachmentsForLoggedInCustomer(): void
    {
        $this->login('customer@example.com');
        $attachmentList = $this->attachmentViewModel->getAttachments();
        $attachmentList = array_values($attachmentList);

        $this->assertCount(2, $attachmentList);

        $attachmentPrefix = ['first', 'second'];
        foreach ($attachmentList as $i => $attachment) {
            $attachment = $this->attachmentRepository->getById($attachment->getId());

            $this->assertEquals(sprintf('%s_attachment', $attachmentPrefix[$i]), $attachment->getName());
            $this->assertEquals('magento_image.jpg', $attachment->getFilename());
            $this->assertEquals(sprintf('%s_attachment_description', $attachmentPrefix[$i]), $attachment->getDescription());
            $this->assertEquals(10 + $i, $attachment->getSortOrder());
        }
    }

    protected function login($email)
    {
        $customer = $this->customer;
        $customer->setWebsiteId(1);
        $customer->loadByEmail($email);

        $this->customerSession->loginById($customer->getId());
    }
}
