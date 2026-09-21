<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\Config\Source;

class StoreAssignmentMode implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            [
                'value' => \MageSuite\FileAttachments\Model\Attachment\StoreAssignmentMode::ADD->value,
                'label' => __('Add to current store views')
            ],
            [
                'value' => \MageSuite\FileAttachments\Model\Attachment\StoreAssignmentMode::REPLACE->value,
                'label' => __('Replace current store views')
            ]
        ];
    }
}
