<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Controller\Adminhtml\Attachment;

class Delete extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'MageSuite_FileAttachments::attachment';

    protected \MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository
    ) {
        parent::__construct($context);

        $this->attachmentRepository = $attachmentRepository;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int)$this->getRequest()->getParam('attachment_id');

        if ($id) {
            try {
                $this->attachmentRepository->deleteById($id);
                $this->messageManager->addSuccess(__('You deleted the attachment.'));

                return $resultRedirect->setPath('*/*/index');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', ['attachment_id' => $id]);
            }
        }

        $this->messageManager->addError(__('We can\'t find a attachment to delete.'));

        return $resultRedirect->setPath('*/*/index');
    }
}
