<?php
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$attachment = $objectManager->create(\MageSuite\FileAttachments\Api\Data\AttachmentInterface::class);
$attachmentRepository = $objectManager->create(\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface::class);
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);

$stores = [];
foreach ($storeManager->getStores() as $store) {
    $stores[] = $store->getId();
}

$attachment->setFilename('magento_image.jpg')
    ->setName('Name')
    ->setDescription('description')
    ->setSortOrder(10)
    ->setStoreIds($stores);
$attachmentRepository->save($attachment);
$fixtureDir = realpath(__DIR__ . '/../assets') . '/';
$attachmentDir = dirname($attachment->getFilePath());

if (!is_dir($attachmentDir)) {
    mkdir($attachmentDir, 0777, true);
}

copy($fixtureDir  . $attachment->getFilename(), $attachment->getFilePath());
$registry->unregister('attachmentId');
$registry->register('attachmentId', $attachment->getId());

/** @var \Magento\Customer\Api\GroupRepositoryInterface $groupRepository */
$groupRepository = $objectManager->create(
    \Magento\Customer\Api\GroupRepositoryInterface::class
);

$searchCriteriaBuilder = $objectManager->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
$groupList = $groupRepository->getList($searchCriteriaBuilder->create())->getItems();


$attachmentKeys = [
    [
        'code' => 'first_attachment',
        'customer_group_ids' => [0,1]
    ],
    [
        'code' => 'second_attachment',
        'customer_group_ids' => [1,2]
    ],
    [
        'code' => 'third_attachment',
        'customer_group_ids' => [2,3]
    ]
];

foreach ($attachmentKeys as $i => $attachmentKey) {
    $attachment = $objectManager->create(\MageSuite\FileAttachments\Api\Data\AttachmentInterface::class);
    $attachmentRepository = $objectManager->create(\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface::class);
    $attachment->setFilename('magento_image.jpg')
        ->setName($attachmentKey['code'])
        ->setDescription(sprintf('%s_description', $attachmentKey['code']))
        ->setSortOrder(10 + $i)
        ->setCustomerGroupIds($attachmentKey['customer_group_ids'])
        ->setStoreIds($stores);
    $attachmentRepository->save($attachment);
    $fixtureDir = realpath(__DIR__ . '/../assets') . '/';
    $attachmentDir = dirname($attachment->getFilePath());

    if (!is_dir($attachmentDir)) {
        mkdir($attachmentDir, 0777, true);
    }

    copy($fixtureDir  . $attachment->getFilename(), $attachment->getFilePath());
    $registry->unregister(sprintf('%s_id', $attachmentKey['code']));
    $registry->register(sprintf('%s_id', $attachmentKey['code']), $attachment->getId());
}
