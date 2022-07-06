<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Block\Adminhtml\Attachment\Edit;

class DeleteButton extends GenericButton implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    public function getButtonData(): array
    {
        $attachmentId = $this->getAttachmentId();

        if (!$attachmentId) {
            return [];
        }

        $confirmMessage = __('Are you sure you want to do this?');
        $data = [
            'label' => __('Delete Attachment'),
            'class' => 'delete',
            'on_click' => sprintf(
                "deleteConfirm('%s', '%s')",
                $confirmMessage,
                $this->urlBuilder->getUrl('*/*/delete', ['attachment_id' => $attachmentId])
            ),
            'sort_order' => 20,
        ];

        return $data;
    }
}
