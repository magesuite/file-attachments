<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Controller\Adminhtml\Attachment;

class MassAssignStores extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'MageSuite_FileAttachments::attachment';

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        protected \Magento\Ui\Component\MassAction\Filter $filter,
        protected \MageSuite\FileAttachments\Model\ResourceModel\Attachment\CollectionFactory $collectionFactory,
        protected \Magento\Framework\Registry $registry,
        protected \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        parent::__construct($context);
    }

    public function execute(): \Magento\Framework\Controller\ResultInterface
    {
        try {
            $attachmentIds = $this->filter->getCollection($this->collectionFactory->create())->getAllIds();
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());

            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        }

        $this->registry->register(
            \MageSuite\FileAttachments\Ui\Component\Form\Field\SelectedAttachmentIds::REGISTRY_KEY,
            array_map('intval', $attachmentIds)
        );

        $resultPage = $this->pageFactory->create();
        $resultPage->setActiveMenu('MageSuite_FileAttachments::attachment_menu');
        $resultPage->getConfig()->getTitle()->prepend(
            __('Assign Store Views to %1 attachment(s)', count($attachmentIds))
        );

        return $resultPage;
    }
}
