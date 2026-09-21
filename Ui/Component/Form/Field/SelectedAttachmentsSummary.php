<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Ui\Component\Form\Field;

class SelectedAttachmentsSummary extends \Magento\Ui\Component\Form\Field
{
    public const PREVIEW_LIMIT = 10;

    public const MAX_LENGTH = 300;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        protected \Magento\Framework\Registry $registry,
        protected \MageSuite\FileAttachments\Model\ResourceModel\Attachment\CollectionFactory $collectionFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepare(): void
    {
        parent::prepare();

        $config = (array)$this->getData('config');
        $config['default'] = $this->buildSummary();
        $this->setData('config', $config);
    }

    protected function buildSummary(): string
    {
        $attachmentIds = (array)$this->registry->registry(
            \MageSuite\FileAttachments\Ui\Component\Form\Field\SelectedAttachmentIds::REGISTRY_KEY
        );

        if (empty($attachmentIds)) {
            return '';
        }

        $names = $this->getAttachmentNames(array_slice($attachmentIds, 0, self::PREVIEW_LIMIT));
        $summary = $this->truncate(implode(', ', $names));
        $remaining = count($attachmentIds) - count($names);

        if ($remaining > 0) {
            $summary .= (string)__(' and %1 more', $remaining);
        }

        return $summary;
    }

    protected function getAttachmentNames(array $attachmentIds): array
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect(['attachment_id', 'name', 'filename']);
        $collection->addFieldToFilter('attachment_id', ['in' => $attachmentIds]);
        $names = [];

        foreach ($collection as $attachment) {
            $names[] = $attachment->getName() ?: $attachment->getFilename();
        }

        return $names;
    }

    protected function truncate(string $summary): string
    {
        if (mb_strlen($summary) <= self::MAX_LENGTH) {
            return $summary;
        }

        return mb_substr($summary, 0, self::MAX_LENGTH) . '...';
    }
}
