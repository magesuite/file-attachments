<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Block\Adminhtml\Attachment\Edit;

class ResetButton extends GenericButton implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    public function getButtonData(): array
    {
        $data = [
            'label' => __('Reset'),
            'class' => 'reset',
            'on_click' => 'location.reload();',
            'sort_order' => 30,
        ];

        return $data;
    }
}
