<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Controller\Attachment;

class Download extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        protected \MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository,
        protected \MageSuite\FileAttachments\Model\FileUploader $fileUploader,
        protected \MageSuite\FileAttachments\Service\CustomerGroupValidator $customerGroupValidator,
        protected \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        protected \MageSuite\FileAttachments\Service\HashAttachmentFilename $hashAttachmentFilename,
        protected \Magento\Framework\UrlInterface $url,
        protected \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    public function execute(): \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
    {
        try {
            $fileNameHash = (string)$this->getRequest()->getParam('file');
            $id = (int)$this->getRequest()->getParam('id');
            $attachment = $this->attachmentRepository->getById($id);

            if (!$this->validateAttachment($attachment, $fileNameHash)) {
                $this->messageManager->addErrorMessage(__('Attachment does not exist.'));
                return $this->redirectToReferer();
            }

            if (!$this->customerGroupValidator->isValid($attachment)) {
                $this->messageManager->addErrorMessage(__('You are not allowed to download this file.'));
                return $this->redirectToReferer();
            }

            $fileName = $attachment->getFilename();

            $fileContent = ['type' => 'filename', 'value' => sprintf('%s/%s', $this->fileUploader->getBasePath(), $fileName)];

            $response = $this->fileFactory->create(
                $fileName,
                $fileContent,
                \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR
            );

            $response->setHeader('Cache-Control', 'no-cache');

            return $response;
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Error during attachment download: %s', $e->getMessage()));
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

    protected function validateAttachment(\MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachment, string $fileNameHash): bool
    {
        $generatedFileNameHash = $this->hashAttachmentFilename->getHashFromFilename($attachment->getFilename());
        if (!$attachment->getId() || $generatedFileNameHash !== $fileNameHash) {
            return false;
        }

        return true;
    }
}
