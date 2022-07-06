<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Block\Adminhtml\Attachment\Grid\Column\Renderer;

class Preview extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row): string
    {
        $fileUrl = $row->getFileUrl();

        return sprintf(
            '<a href="%s" target="_blank">%s</a>',
            $fileUrl,
            __('View')
        );
    }
}
