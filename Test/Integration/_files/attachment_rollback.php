<?php

declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$attachmentRepository = $objectManager->get(\MageSuite\FileAttachments\Api\AttachmentRepositoryInterface::class);

$registryKeys = ['attachmentId', 'first_attachment_id', 'second_attachment_id', 'third_attachment_id'];

foreach ($registryKeys as $registryKey) {
    $attachmentId = $registry->registry($registryKey);

    if (!$attachmentId) {
        continue;
    }

    try {
        $attachmentRepository->deleteById((int)$attachmentId);
    } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
        // Attachment already removed
    }

    $registry->unregister($registryKey);
}
