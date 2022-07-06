<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Controller\Adminhtml\Attachment;

class Save extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'MageSuite_FileAttachments::attachment';

    protected \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor;

    protected \MageSuite\FileAttachments\Model\AttachmentFactory $attachmentFactory;

    protected \MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository;

    protected \MageSuite\FileAttachments\Model\FileUploader $fileUploader;

    protected \Psr\Log\LoggerInterface $logger;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \MageSuite\FileAttachments\Model\AttachmentFactory $attachmentFactory,
        \MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository,
        \MageSuite\FileAttachments\Model\FileUploader $fileUploader,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->attachmentFactory = $attachmentFactory;
        $this->attachmentRepository = $attachmentRepository;
        $this->fileUploader = $fileUploader;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $id = (int)$this->getRequest()->getParam('attachment_id');
            $model = $this->attachmentFactory->create();

            if ($id) {
                try {
                    /** @var \MageSuite\FileAttachments\Model\Attachment $model */
                    $model = $this->attachmentRepository->getById($id);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $this->logger->critical($e);
                    $this->messageManager->addErrorMessage(__('This attachment no longer exists.'));
                    return $resultRedirect->setPath('*/*/index');
                }
            }

            if (empty($data['attachment_id'])) {
                $data['attachment_id'] = null;
            }

            if (isset($data[$model::FILENAME][0]['name']) && isset($data[$model::FILENAME][0]['tmp_name'])) {
                $data[$model::FILENAME] = $data[$model::FILENAME][0]['name'];
                $this->fileUploader->moveFileFromTmp($data[$model::FILENAME]);
            } elseif (isset($data[$model::FILENAME][0]['name']) && !isset($data[$model::FILENAME][0]['tmp_name'])) {
                $data[$model::FILENAME] = $data[$model::FILENAME][0]['name'];
            } else {
                $data[$model::FILENAME] = '';
            }

            $model->addData($data);

            try {
                $this->attachmentRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the attachment.'));
                $this->dataPersistor->clear('fileattachments_attachment_attachment');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['attachment_id' => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/index');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the attachment.'));
            }

            $this->dataPersistor->set('fileattachments_attachment_attachment', $data);

            return $resultRedirect->setPath('*/*/edit', ['attachment_id' => $id]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
