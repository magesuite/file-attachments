<?php
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$attachmentId = $registry->registry('attachmentId');
$attachmentId = $registry->registry('first_attachment');
$attachmentId = $registry->registry('second_attachment');
$attachmentId = $registry->registry('third_attachment');
$attachmentRepository = $objectManager->get(\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface::class);
$attachmentRepository->deleteById($attachmentId);
