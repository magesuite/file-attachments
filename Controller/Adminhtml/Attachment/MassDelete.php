<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Controller\Adminhtml\Attachment;

class MassDelete extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'MageSuite_FileAttachments::attachment';

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        protected \Magento\Ui\Component\MassAction\Filter $filter,
        protected \MageSuite\FileAttachments\Model\ResourceModel\Attachment\CollectionFactory $collectionFactory,
        protected \MageSuite\FileAttachments\Model\Attachment\DeleteMultiple $deleteMultiple
    ) {
        parent::__construct($context);
    }

    public function execute(): \Magento\Framework\Controller\ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $attachmentIds = $this->filter->getCollection($this->collectionFactory->create())->getAllIds();
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());

            return $resultRedirect->setPath('*/*/index');
        }

        $result = $this->deleteMultiple->execute($attachmentIds);
        $deletedCount = count($result->getDeletedIds());
        $failedCount = count($result->getFailedIds());

        if ($deletedCount) {
            $this->messageManager->addSuccessMessage(
                __('A total of %1 attachment(s) have been deleted.', $deletedCount)
            );
        }

        if ($failedCount) {
            $this->messageManager->addErrorMessage(
                __('%1 attachment(s) could not be deleted. Please check the logs for details.', $failedCount)
            );
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
