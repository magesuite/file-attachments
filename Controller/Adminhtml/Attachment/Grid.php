<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Controller\Adminhtml\Attachment;

class Grid extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    const ADMIN_RESOURCE = 'MageSuite_FileAttachments::attachment';

    protected \Magento\Framework\Controller\Result\RawFactory $resultRawFactory;

    protected \Magento\Framework\View\LayoutFactory $layoutFactory;

    protected \Magento\Framework\Registry $registry;

    protected \Magento\Catalog\Api\ProductRepositoryInterface $productRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
    }

    public function execute()
    {
        $this->initProduct();
        $resultRaw = $this->resultRawFactory->create();

        return $resultRaw->setContents(
            $this->layoutFactory->create()->createBlock(
                \MageSuite\FileAttachments\Block\Adminhtml\Attachment\Tab\Attachment::class,
                'file.attachments.grid'
            )->toHtml()
        );
    }

    protected function initProduct(): void
    {
        $productId = (int)$this->getRequest()->getParam('id');
        $product = $this->productRepository->getById($productId);
        $this->registry->register('current_product', $product);
    }
}
