<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Test\Integration\Model;

#[\Magento\TestFramework\Fixture\AppIsolation(true)]
#[\Magento\TestFramework\Fixture\DbIsolation(true)]
class AttachmentRepositoryTest extends \PHPUnit\Framework\TestCase
{
    protected const int GENERAL_CUSTOMER_GROUP_ID = 1;

    protected const int WHOLESALE_CUSTOMER_GROUP_ID = 2;

    protected ?\Magento\TestFramework\ObjectManager $objectManager;

    protected ?\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository;

    protected ?\Magento\Framework\Registry $registry;

    protected ?\Magento\Customer\Model\Session $customerSession;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->attachmentRepository = $this->objectManager->get(\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface::class);
        $this->registry = $this->objectManager->get(\Magento\Framework\Registry::class);
        $this->customerSession = $this->objectManager->get(\Magento\Customer\Model\Session::class);
    }

    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_FileAttachments::Test/Integration/_files/attachment.php')]
    public function testItGeneratesProperThumbnailImage(): void
    {
        $attachmentId = $this->registry->registry('attachmentId');
        $attachment = $this->attachmentRepository->getById((int)$attachmentId);

        $this->assertEquals('Name', $attachment->getName());
        $this->assertEquals('magento_image.jpg', $attachment->getFilename());
        $this->assertEquals('description', $attachment->getDescription());
        $this->assertEquals('10', $attachment->getSortOrder());
    }

    #[\Magento\TestFramework\Fixture\DataFixture('MageSuite_FileAttachments::Test/Integration/_files/attachment.php')]
    public function testItReturnCorrectGroupsForAttachments(): void
    {
        $attachmentId = $this->registry->registry('first_attachment_id');
        $attachment = $this->attachmentRepository->getById((int)$attachmentId);

        $this->assertEquals('first_attachment', $attachment->getName());
        $this->assertEquals('magento_image.jpg', $attachment->getFilename());
        $this->assertEquals('first_attachment_description', $attachment->getDescription());
        $this->assertEquals('10', $attachment->getSortOrder());
        $this->assertEquals([0, 1], $attachment->getCustomerGroupIds());

        $attachmentId = $this->registry->registry('second_attachment_id');
        $attachment = $this->attachmentRepository->getById((int)$attachmentId);

        $this->assertEquals('second_attachment', $attachment->getName());
        $this->assertEquals('magento_image.jpg', $attachment->getFilename());
        $this->assertEquals('second_attachment_description', $attachment->getDescription());
        $this->assertEquals('11', $attachment->getSortOrder());
        $this->assertEquals([1, 2], $attachment->getCustomerGroupIds());

        $attachmentId = $this->registry->registry('third_attachment_id');
        $attachment = $this->attachmentRepository->getById((int)$attachmentId);

        $this->assertEquals('third_attachment', $attachment->getName());
        $this->assertEquals('magento_image.jpg', $attachment->getFilename());
        $this->assertEquals('third_attachment_description', $attachment->getDescription());
        $this->assertEquals('12', $attachment->getSortOrder());
        $this->assertEquals([2, 3], $attachment->getCustomerGroupIds());
    }

    #[\Magento\TestFramework\Fixture\AppArea('frontend')]
    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['name' => 'Group visibility general', 'customer_group_ids' => [self::GENERAL_CUSTOMER_GROUP_ID]])]
    public function testGuestDoesNotSeeAttachmentsRestrictedToOtherGroups(): void
    {
        $this->customerSession->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);

        $this->assertEquals([], $this->getVisibleAttachmentNames());
    }

    #[\Magento\TestFramework\Fixture\AppArea('frontend')]
    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['name' => 'Group visibility general', 'customer_group_ids' => [self::GENERAL_CUSTOMER_GROUP_ID]])]
    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['name' => 'Group visibility wholesale', 'customer_group_ids' => [self::WHOLESALE_CUSTOMER_GROUP_ID]])]
    public function testCustomerSeesOnlyAttachmentsAssignedToTheirGroup(): void
    {
        $this->customerSession->setCustomerGroupId(self::GENERAL_CUSTOMER_GROUP_ID);

        $this->assertEquals(['Group visibility general'], $this->getVisibleAttachmentNames());
    }

    protected function getVisibleAttachmentNames(): array
    {
        $searchCriteriaBuilder = $this->objectManager->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->addFilter('name', 'Group visibility%', 'like');
        $searchResults = $this->attachmentRepository->getList($searchCriteriaBuilder->create());

        return array_values(array_map(
            fn(\MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachment) => $attachment->getName(),
            $searchResults->getItems()
        ));
    }
}
