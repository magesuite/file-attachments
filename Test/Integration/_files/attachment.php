<?php
declare(strict_types=1);
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$attachment = $objectManager->get(\MageSuite\FileAttachments\Api\Data\AttachmentInterface::class);
$attachmentRepository = $objectManager->get(\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface::class);
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$attachment->setFilename('magento_image.jpg')
    ->setName('Name')
    ->setDescription('description')
    ->setSortOrder(10);
$attachmentRepository->save($attachment);
$fixtureDir = realpath(__DIR__ . '/../assets') . '/';
$attachmentDir = dirname($attachment->getFilePath());

if (!is_dir($attachmentDir)) {
    mkdir($attachmentDir, 0777, true);
}

copy($fixtureDir  . $attachment->getFilename(), $attachment->getFilePath());
$registry->unregister('attachmentId');
$registry->register('attachmentId', $attachment->getId());
