<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Controller\Adminhtml\Attachment;

class Index extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'MageSuite_FileAttachments::attachment';

    protected \Magento\Framework\View\Result\PageFactory $pageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->pageFactory->create();
        $resultPage->setActiveMenu('MageSuite_FileAttachments::attachment_menu');
        $resultPage->getConfig()->getTitle()->prepend(__('File Attachments'));

        return $resultPage;
    }
}
