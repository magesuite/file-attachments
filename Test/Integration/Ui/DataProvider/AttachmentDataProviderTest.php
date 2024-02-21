<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Test\Integration\Ui\DataProvider;

/**
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class AttachmentDataProviderTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\FileAttachments\Ui\DataProvider\AttachmentDataProvider $attachmentDataProvider;

    protected ?\Magento\Framework\Registry $registry;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->attachmentDataProvider = $objectManager->create(
            \MageSuite\FileAttachments\Ui\DataProvider\AttachmentDataProvider::class,
            [
                'name' => 'fileattachments_attachment_attachment',
                'primaryFieldName' => 'attachment_id',
                'requestFieldName' => 'attachment_id'
            ]
        );
        $this->registry = $objectManager->get(\Magento\Framework\Registry::class);
    }

    /**
     * @magentoDataFixture MageSuite_FileAttachments::Test/Integration/_files/attachment.php
     */
    public function testItReturnCorrectDataForProvider(): void
    {
        $providerData = $this->attachmentDataProvider->getData();

        $expectedAttachmentData = $this->getExpectedAttachmentData();
        foreach ($providerData as $attachmentData) {
            $expectedDataByKey = $expectedAttachmentData[$attachmentData['name']];
            $this->assertEquals($expectedDataByKey['name'], $attachmentData['name']);
            $this->assertEquals($expectedDataByKey['filename'], $attachmentData['filename'][0]['name']);
            $this->assertEquals($expectedDataByKey['description'], $attachmentData['description']);
            $this->assertEquals($expectedDataByKey['customer_group_ids'], $attachmentData['customer_group_ids']);
        }
    }

    protected function getExpectedAttachmentData(): array
    {
        return [
            'Name' => [
                'name' => 'Name',
                'filename' => 'magento_image.jpg',
                'description' => 'description',
                'sort_order' => '10',
                'customer_group_ids' => [0, 1, 2, 3]
            ],
            'first_attachment' => [
                'name' => 'first_attachment',
                'filename' => 'magento_image.jpg',
                'description' => 'first_attachment_description',
                'sort_order' => '10',
                'customer_group_ids' => [0, 1]
            ],
            'second_attachment' => [
                'name' => 'second_attachment',
                'filename' => 'magento_image.jpg',
                'description' => 'second_attachment_description',
                'sort_order' => '11',
                'customer_group_ids' => [1, 2]
            ],
            'third_attachment' => [
                'name' => 'third_attachment',
                'filename' => 'magento_image.jpg',
                'description' => 'third_attachment_description',
                'sort_order' => '12',
                'customer_group_ids' => [2, 3]
            ]
        ];
    }
}
