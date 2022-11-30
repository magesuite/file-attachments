<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Controller\Adminhtml\Attachment\File;

class Upload extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    protected \MageSuite\FileAttachments\Model\FileUploader $fileUploader;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \MageSuite\FileAttachments\Model\FileUploader $fileUploader
    ) {
        parent::__construct($context);

        $this->fileUploader = $fileUploader;
    }

    public function execute(): \Magento\Framework\Controller\ResultInterface
    {
        $fileNameId = $this->getRequest()->getParam('param_name', 'filename');

        try {
            $result = $this->fileUploader->saveFileToTmpDir($fileNameId);
        } catch (\Exception $e) {
            $result = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode()
            ];
        }

        return $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->setData($result);
    }
}
