<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Block\Adminhtml\Attachment\MassAssignStores;

class AssignButton implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    public function getButtonData(): array
    {
        return [
            'label' => __('Assign'),
            'class' => 'save primary',
            'on_click' => '',
            'sort_order' => 90
        ];
    }
}
