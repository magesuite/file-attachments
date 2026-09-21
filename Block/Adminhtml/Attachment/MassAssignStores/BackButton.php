<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Block\Adminhtml\Attachment\MassAssignStores;

class BackButton implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    public function __construct(
        protected \Magento\Framework\UrlInterface $urlBuilder
    ) {
    }

    public function getButtonData(): array
    {
        return [
            'label' => __('Back'),
            'class' => 'back',
            'on_click' => sprintf("location.href = '%s';", $this->urlBuilder->getUrl('*/*/index')),
            'sort_order' => 10
        ];
    }
}
