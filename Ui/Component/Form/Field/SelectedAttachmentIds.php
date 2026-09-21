<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Ui\Component\Form\Field;

class SelectedAttachmentIds extends \Magento\Ui\Component\Form\Field
{
    public const REGISTRY_KEY = 'fileattachments_selected_attachment_ids';

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        protected \Magento\Framework\Registry $registry,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepare(): void
    {
        parent::prepare();

        $config = (array)$this->getData('config');
        $config['default'] = implode(',', (array)$this->registry->registry(self::REGISTRY_KEY));
        $this->setData('config', $config);
    }
}
