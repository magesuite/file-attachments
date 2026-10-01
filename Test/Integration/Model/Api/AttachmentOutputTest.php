<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Test\Integration\Model\Api;

#[\Magento\TestFramework\Fixture\AppArea('webapi_rest')]
#[\Magento\TestFramework\Fixture\DbIsolation(true)]
class AttachmentOutputTest extends \PHPUnit\Framework\TestCase
{
    protected const MASKED_FIELDS = ['file_path', 'thumbnail_path', 'file_url', 'allowed_customer_group_ids'];

    protected const EXPOSED_FIELDS = ['attachment_id', 'name', 'store_ids', 'customer_group_ids', 'product_skus', 'download_url', 'thumbnail_url'];

    protected ?\Magento\TestFramework\ObjectManager $objectManager;

    protected ?\Magento\Framework\Webapi\ServiceOutputProcessor $serviceOutputProcessor;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->objectManager->removeSharedInstance(\Magento\Framework\Reflection\DataObjectProcessor::class);
        $this->serviceOutputProcessor = $this->objectManager->create(\Magento\Framework\Webapi\ServiceOutputProcessor::class);
    }

    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['name' => 'Output attachment'], 'attachment')]
    public function testItMasksInternalFieldsInSingleAttachmentOutput(): void
    {
        $attachmentId = (int)\Magento\TestFramework\Fixture\DataFixtureStorageManager::getStorage()->get('attachment')->getId();
        $attachmentRepository = $this->objectManager->create(\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface::class);

        $output = $this->serviceOutputProcessor->process(
            $attachmentRepository->getById($attachmentId),
            \MageSuite\FileAttachments\Api\AttachmentManagementInterface::class,
            'save'
        );

        $this->assertOutputFields($output);
    }

    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['name' => 'Output list attachment'])]
    public function testItMasksInternalFieldsInListOutput(): void
    {
        $searchCriteriaBuilder = $this->objectManager->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->addFilter('name', 'Output list attachment');
        $attachmentManagement = $this->objectManager->create(\MageSuite\FileAttachments\Api\AttachmentManagementInterface::class);

        $output = $this->serviceOutputProcessor->process(
            $attachmentManagement->getList($searchCriteriaBuilder->create()),
            \MageSuite\FileAttachments\Api\AttachmentManagementInterface::class,
            'getList'
        );

        $this->assertCount(1, $output['items']);
        $this->assertOutputFields($output['items'][0]);
    }

    protected function assertOutputFields(array $attachmentOutput): void
    {
        foreach (self::MASKED_FIELDS as $maskedField) {
            $this->assertArrayNotHasKey($maskedField, $attachmentOutput);
        }

        foreach (self::EXPOSED_FIELDS as $exposedField) {
            $this->assertArrayHasKey($exposedField, $attachmentOutput);
        }
    }
}
