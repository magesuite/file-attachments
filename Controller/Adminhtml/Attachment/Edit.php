<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Controller\Adminhtml\Attachment;

class Edit extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'MageSuite_FileAttachments::attachment';

    protected \Magento\Framework\View\Result\PageFactory $resultPageFactory;

    protected \MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->attachmentRepository = $attachmentRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('attachment_id');

        if ($id) {
            $attachment = $this->attachmentRepository->getById($id);

            if (!$attachment->getId()) {
                $this->messageManager->addErrorMessage(__('This attachment no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $label = $id ? __('Edit Attachment') : __('New Attachment');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(
            $label,
            $label
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Attachments'));
        $resultPage->getConfig()->getTitle()->prepend($label);

        return $resultPage;
    }
}
