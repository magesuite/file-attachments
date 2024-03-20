<?php

namespace MageSuite\FileAttachments\Controller\Attachment;

class Download extends \Magento\Framework\App\Action\Action
{
    protected \MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository;
    protected \MageSuite\FileAttachments\Model\FileUploader $fileUploader;
    protected \MageSuite\FileAttachments\Service\CustomerGroupValidator $customerGroupValidator;
    protected \Magento\Framework\App\Response\Http\FileFactory $fileFactory;
    protected \Magento\Framework\UrlInterface $url;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository,
        \MageSuite\FileAttachments\Model\FileUploader $fileUploader,
        \MageSuite\FileAttachments\Service\CustomerGroupValidator $customerGroupValidator,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\UrlInterface $url
    ) {
        parent::__construct($context);
        $this->attachmentRepository = $attachmentRepository;
        $this->fileUploader = $fileUploader;
        $this->customerGroupValidator = $customerGroupValidator;
        $this->fileFactory = $fileFactory;
        $this->url = $url;
    }

    public function execute()
    {
        try {
            $file = $this->getRequest()->getParam('file');
            $id = $this->getRequest()->getParam('id');
            $attachment = $this->attachmentRepository->getById($id);

            if (!$this->validateAttachment($attachment, $file)) {
                $this->messageManager->addErrorMessage(__('Attachment does not exist.'));
                return $this->redirectToReferer();
            }

            if (!$this->customerGroupValidator->isValid($attachment->getAttachmentId())) {
                $this->messageManager->addErrorMessage(__('You are not allowed to download this file.'));
                return $this->redirectToReferer();
            }

            $fileName = $attachment->getFilename();

            $fileContent = ['type' => 'filename', 'value' => sprintf('%s/%s', $this->fileUploader->getBasePath(), $fileName)];

            return $this->fileFactory->create(
                $fileName,
                $fileContent,
                \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Something went wrong. Please try again later.'));
            return $this->redirectToReferer();
        }
    }

    protected function redirectToReferer(): \Magento\Framework\Controller\Result\Redirect
    {
        $refererUrl = $this->_redirect->getRefererUrl();
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($refererUrl);

        return $resultRedirect;
    }

    protected function validateAttachment(\MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachment, string $file): bool
    {
        if (!$attachment->getId() || $attachment->getFilename() !== $file) {
            return false;
        }

        return true;
    }
}
