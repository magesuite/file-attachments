<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Test\Integration\Model\Api;

#[\Magento\TestFramework\Fixture\DbIsolation(true)]
class AttachmentManagementTest extends \PHPUnit\Framework\TestCase
{
    public const FIXTURE_FILENAME = 'magento_image.jpg';

    protected ?\Magento\TestFramework\ObjectManager $objectManager;

    protected ?\MageSuite\FileAttachments\Api\AttachmentManagementInterface $attachmentManagement;

    protected ?\MageSuite\FileAttachments\Model\ResourceModel\Attachment $attachmentResource;

    protected array $createdAttachmentIds = [];

    protected array $createdFilePaths = [];

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->objectManager->removeSharedInstance(\MageSuite\FileAttachments\Model\AttachmentRepository::class);
        $this->attachmentManagement = $this->objectManager->create(\MageSuite\FileAttachments\Api\AttachmentManagementInterface::class);
        $this->attachmentResource = $this->objectManager->get(\MageSuite\FileAttachments\Model\ResourceModel\Attachment::class);
    }

    protected function tearDown(): void
    {
        foreach ($this->createdAttachmentIds as $attachmentId) {
            $attachment = $this->loadAttachment($attachmentId);
            $this->createdFilePaths[] = $attachment->getFilePath();
            $this->createdFilePaths[] = $attachment->getThumbnailPath();
        }

        foreach (array_unique($this->createdFilePaths) as $filePath) {
            if (!is_file($filePath)) {
                continue;
            }

            unlink($filePath);
        }

        $this->createdAttachmentIds = [];
        $this->createdFilePaths = [];
    }

    public function testItCreatesAttachmentWithFileAndAssignments(): void
    {
        $apiAttachment = $this->createApiAttachment('Created attachment', [1], [0, 1]);

        $attachment = $this->loadAttachment($apiAttachment->getAttachmentId());
        $this->assertEquals('Created attachment', $attachment->getName());
        $this->assertMatchesRegularExpression('/^magento_image(_\d+)?\.jpg$/', $attachment->getFilename());
        $this->assertFileExists($attachment->getFilePath());
        $this->assertFileExists($attachment->getThumbnailPath());
        $this->assertEquals([1], $this->toIntegers($this->attachmentResource->lookupStoreIds($attachment->getId())));
        $this->assertEquals([0, 1], $this->toIntegers($this->attachmentResource->lookupCustomerGroupIds($attachment->getId())));
        $this->assertEquals([1], $apiAttachment->getStoreIds());
        $this->assertEquals([0, 1], $apiAttachment->getCustomerGroupIds());
        $this->assertNotEmpty($apiAttachment->getDownloadUrl());
        $this->assertNotEmpty($apiAttachment->getCreatedAt());
    }

    public function testItAssignsAllCustomerGroupsOnCreate(): void
    {
        $attachmentData = $this->getAttachmentData('All groups on create', [0], null);
        $attachmentData->setAllCustomerGroups(true);

        $apiAttachment = $this->attachmentManagement->save($attachmentData);
        $this->createdAttachmentIds[] = $apiAttachment->getAttachmentId();

        $this->assertEquals(
            $this->getAllCustomerGroupIds(),
            $this->toIntegers($this->attachmentResource->lookupCustomerGroupIds($apiAttachment->getAttachmentId()))
        );
    }

    public function testItRejectsInvalidBase64Content(): void
    {
        $attachmentData = $this->getAttachmentData('Invalid content', [0], [0]);
        $attachmentData->setFileContent($this->getFileContent('not base64 content!', 'file.jpg'));

        $this->expectException(\Magento\Framework\Exception\InputException::class);

        $this->attachmentManagement->save($attachmentData);
    }

    public function testItKeepsAssignmentsAndFileWhenUpdatingOnlyName(): void
    {
        $apiAttachment = $this->createApiAttachment('Before update', [1], [1, 2]);
        $filenameBeforeUpdate = $apiAttachment->getFilename();

        $attachmentData = $this->objectManager->create(\MageSuite\FileAttachments\Api\Data\AttachmentInterface::class);
        $attachmentData->setName('After update');
        $updatedApiAttachment = $this->attachmentManagement->save($attachmentData, $apiAttachment->getAttachmentId());

        $attachment = $this->loadAttachment($apiAttachment->getAttachmentId());
        $this->assertEquals('After update', $attachment->getName());
        $this->assertEquals($filenameBeforeUpdate, $attachment->getFilename());
        $this->assertFileExists($attachment->getFilePath());
        $this->assertEquals([1], $this->toIntegers($this->attachmentResource->lookupStoreIds($attachment->getId())));
        $this->assertEquals([1, 2], $this->toIntegers($this->attachmentResource->lookupCustomerGroupIds($attachment->getId())));
        $this->assertEquals([1, 2], $updatedApiAttachment->getCustomerGroupIds());
    }

    public function testItReplacesFileOnUpdate(): void
    {
        $apiAttachment = $this->createApiAttachment('Replace file', [0], [0]);
        $previousAttachment = $this->loadAttachment($apiAttachment->getAttachmentId());
        $previousFilePath = $previousAttachment->getFilePath();
        $previousThumbnailPath = $previousAttachment->getThumbnailPath();
        $this->createdFilePaths[] = $previousFilePath;
        $this->createdFilePaths[] = $previousThumbnailPath;

        $attachmentData = $this->objectManager->create(\MageSuite\FileAttachments\Api\Data\AttachmentInterface::class);
        $attachmentData->setFileContent($this->getFileContent($this->getFixtureFileData(), 'replaced_file.jpg'));
        $this->attachmentManagement->save($attachmentData, $apiAttachment->getAttachmentId());

        $attachment = $this->loadAttachment($apiAttachment->getAttachmentId());
        $this->assertMatchesRegularExpression('/^replaced_file(_\d+)?\.jpg$/', $attachment->getFilename());
        $this->assertFileExists($attachment->getFilePath());
        $this->assertFileExists($attachment->getThumbnailPath());
        $this->assertFileDoesNotExist($previousFilePath);
        $this->assertFileDoesNotExist($previousThumbnailPath);
    }

    public function testItDeletesAttachmentWithFile(): void
    {
        $apiAttachment = $this->createApiAttachment('To delete', [0], [0]);
        $attachment = $this->loadAttachment($apiAttachment->getAttachmentId());
        $filePath = $attachment->getFilePath();
        $this->createdFilePaths[] = $filePath;
        $this->createdAttachmentIds = [];

        $result = $this->attachmentManagement->deleteById($apiAttachment->getAttachmentId());

        $this->assertTrue($result);
        $this->assertFileDoesNotExist($filePath);
        $this->assertNull($this->loadAttachment($apiAttachment->getAttachmentId())->getId());
    }

    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['name' => 'List attachment 1', 'sort_order' => 1, 'customer_group_ids' => [2]])]
    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['name' => 'List attachment 2', 'sort_order' => 2, 'customer_group_ids' => [2]])]
    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['name' => 'List attachment 3', 'sort_order' => 3, 'customer_group_ids' => [2]])]
    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['name' => 'List attachment 4', 'sort_order' => 4, 'customer_group_ids' => [2]])]
    public function testItReturnsPaginatedListWithTotalCount(): void
    {
        $searchCriteriaBuilder = $this->objectManager->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $sortOrderBuilder = $this->objectManager->create(\Magento\Framework\Api\SortOrderBuilder::class);
        $sortOrderBuilder->setField('sort_order');
        $sortOrderBuilder->setAscendingDirection();
        $searchCriteriaBuilder->addFilter('name', 'List attachment%', 'like');
        $searchCriteriaBuilder->addSortOrder($sortOrderBuilder->create());
        $searchCriteriaBuilder->setPageSize(2);
        $searchCriteriaBuilder->setCurrentPage(2);

        $searchResults = $this->attachmentManagement->getList($searchCriteriaBuilder->create());
        $names = array_map(
            fn(\MageSuite\FileAttachments\Api\Data\AttachmentInterface $savedAttachment) => $savedAttachment->getName(),
            $searchResults->getItems()
        );

        $this->assertEquals(4, $searchResults->getTotalCount());
        $this->assertEquals(['List attachment 3', 'List attachment 4'], $names);
        $this->assertEquals([0], $searchResults->getItems()[0]->getStoreIds());
        $this->assertEquals([2], $searchResults->getItems()[0]->getCustomerGroupIds());
    }

    #[\Magento\TestFramework\Fixture\DataFixture(\Magento\Catalog\Test\Fixture\Product::class, ['sku' => 'attachment-second-product'], 'second_product')]
    #[\Magento\TestFramework\Fixture\DataFixture(\Magento\Catalog\Test\Fixture\Product::class, ['sku' => 'attachment-first-product'], 'first_product')]
    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['name' => 'Product skus attachment'], 'attachment')]
    public function testItReturnsSkusOfAssignedProducts(): void
    {
        $fixtures = \Magento\TestFramework\Fixture\DataFixtureStorageManager::getStorage();
        $attachment = $fixtures->get('attachment');

        foreach (['first_product', 'second_product'] as $productFixture) {
            $this->attachmentResource->assignAttachmentsToProduct(
                (int)$fixtures->get($productFixture)->getId(),
                [(int)$attachment->getId()]
            );
        }

        $searchCriteriaBuilder = $this->objectManager->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->addFilter('name', 'Product skus attachment');
        $searchResults = $this->attachmentManagement->getList($searchCriteriaBuilder->create());

        $attachmentData = $this->objectManager->create(\MageSuite\FileAttachments\Api\Data\AttachmentInterface::class);
        $attachmentData->setName('Product skus attachment updated');
        $updatedApiAttachment = $this->attachmentManagement->save($attachmentData, (int)$attachment->getId());

        $expectedSkus = ['attachment-first-product', 'attachment-second-product'];
        $this->assertEquals($expectedSkus, $searchResults->getItems()[0]->getProductSkus());
        $this->assertEquals($expectedSkus, $updatedApiAttachment->getProductSkus());
    }

    #[\Magento\TestFramework\Fixture\DataFixture(\Magento\Catalog\Test\Fixture\Product::class, ['sku' => 'attachment-other-product'], 'other_product')]
    #[\Magento\TestFramework\Fixture\DataFixture(\Magento\Catalog\Test\Fixture\Product::class, ['sku' => 'attachment-filtered-product'], 'filtered_product')]
    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['name' => 'Sku filter matching'], 'filtered_attachment')]
    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['name' => 'Sku filter other product'], 'other_attachment')]
    #[\Magento\TestFramework\Fixture\DataFixture(\MageSuite\FileAttachments\Test\Integration\Fixture\Attachment::class, ['name' => 'Sku filter without products'])]
    public function testItFiltersListByProductSku(): void
    {
        $fixtures = \Magento\TestFramework\Fixture\DataFixtureStorageManager::getStorage();

        $this->attachmentResource->assignAttachmentsToProduct(
            (int)$fixtures->get('filtered_product')->getId(),
            [(int)$fixtures->get('filtered_attachment')->getId()]
        );
        $this->attachmentResource->assignAttachmentsToProduct(
            (int)$fixtures->get('other_product')->getId(),
            [(int)$fixtures->get('other_attachment')->getId()]
        );

        $searchCriteriaBuilder = $this->objectManager->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $searchCriteriaBuilder->addFilter('name', 'Sku filter%', 'like');
        $searchCriteriaBuilder->addFilter('product_sku', 'attachment-filtered-product');

        $searchResults = $this->attachmentManagement->getList($searchCriteriaBuilder->create());

        $this->assertEquals(1, $searchResults->getTotalCount());
        $this->assertEquals('Sku filter matching', $searchResults->getItems()[0]->getName());
    }

    protected function createApiAttachment(
        string $name,
        array $storeIds,
        array $customerGroupIds
    ): \MageSuite\FileAttachments\Api\Data\AttachmentInterface {
        $apiAttachment = $this->attachmentManagement->save($this->getAttachmentData($name, $storeIds, $customerGroupIds));
        $this->createdAttachmentIds[] = $apiAttachment->getAttachmentId();

        return $apiAttachment;
    }

    protected function getAttachmentData(
        string $name,
        array $storeIds,
        ?array $customerGroupIds
    ): \MageSuite\FileAttachments\Api\Data\AttachmentInterface {
        $attachmentData = $this->objectManager->create(\MageSuite\FileAttachments\Api\Data\AttachmentInterface::class);
        $attachmentData->setName($name);
        $attachmentData->setDescription('description');
        $attachmentData->setSortOrder(10);
        $attachmentData->setStoreIds($storeIds);
        $attachmentData->setCustomerGroupIds($customerGroupIds);
        $attachmentData->setFileContent($this->getFileContent($this->getFixtureFileData(), self::FIXTURE_FILENAME));

        return $attachmentData;
    }

    protected function getFileContent(string $fileData, string $name): \MageSuite\FileAttachments\Api\Data\FileContentInterface
    {
        $fileContent = $this->objectManager->create(\MageSuite\FileAttachments\Api\Data\FileContentInterface::class);
        $fileContent->setFileData($fileData);
        $fileContent->setName($name);

        return $fileContent;
    }

    protected function getFixtureFileData(): string
    {
        return base64_encode(file_get_contents(__DIR__ . '/../../assets/' . self::FIXTURE_FILENAME));
    }

    protected function loadAttachment(int $attachmentId): \MageSuite\FileAttachments\Model\Attachment
    {
        $attachment = $this->objectManager->create(\MageSuite\FileAttachments\Model\Attachment::class);
        $this->attachmentResource->load($attachment, $attachmentId);

        return $attachment;
    }

    protected function getAllCustomerGroupIds(): array
    {
        $customerGroupCollection = $this->objectManager->create(\Magento\Customer\Model\ResourceModel\Group\Collection::class);

        return $this->toIntegers($customerGroupCollection->getAllIds());
    }

    protected function toIntegers(array $values): array
    {
        $integers = array_map('intval', $values);
        sort($integers);

        return $integers;
    }
}
