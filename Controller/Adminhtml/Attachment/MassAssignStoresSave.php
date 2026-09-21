<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Controller\Adminhtml\Attachment;

class MassAssignStoresSave extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'MageSuite_FileAttachments::attachment';

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        protected \MageSuite\FileAttachments\Model\Attachment\AssignStores $assignStores
    ) {
        parent::__construct($context);
    }

    public function execute(): \Magento\Framework\Controller\ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $attachmentIds = $this->getAttachmentIds();

        try {
            $this->assignStores->execute($attachmentIds, $this->getStoreIds(), $this->getMode());
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());

            return $resultRedirect->setPath('*/*/index');
        }

        $this->messageManager->addSuccessMessage(
            __('A total of %1 attachment(s) have been updated.', count($attachmentIds))
        );

        return $resultRedirect->setPath('*/*/index');
    }

    protected function getAttachmentIds(): array
    {
        $attachmentIds = (string)$this->getRequest()->getParam('attachment_ids');

        if ($attachmentIds === '') {
            return [];
        }

        return array_values(array_filter(array_map('intval', explode(',', $attachmentIds))));
    }

    protected function getStoreIds(): array
    {
        $storeIds = $this->getRequest()->getParam('store_ids');

        if (!is_array($storeIds)) {
            return [];
        }

        return array_map('intval', $storeIds);
    }

    protected function getMode(): \MageSuite\FileAttachments\Model\Attachment\StoreAssignmentMode
    {
        $mode = \MageSuite\FileAttachments\Model\Attachment\StoreAssignmentMode::tryFrom(
            (string)$this->getRequest()->getParam('mode')
        );

        if ($mode === null) {
            throw new \Magento\Framework\Exception\InputException(__('Please select a valid assignment mode.'));
        }

        return $mode;
    }
}
