<?php
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$attachmentRepository = $objectManager->create(\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface::class);
$searchCriteriaBuilder = $objectManager->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$product = $productRepository->get('simple');
$attachmentList = $attachmentRepository->getList($searchCriteriaBuilder->create())->getItems();

$attachmentIds = [];
foreach ($attachmentList as $attachment) {
    $attachmentIds[] = $attachment->getId();
}

$attachmentResource = $objectManager->create(\MageSuite\FileAttachments\Model\ResourceModel\Attachment::class);

$attachmentResource->assignAttachmentsToProduct((int) $product->getId(), $attachmentIds);

$registry->unregister('current_product');
$registry->register('current_product', $product);
