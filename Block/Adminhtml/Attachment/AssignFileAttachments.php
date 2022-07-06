<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Block\Adminhtml\Attachment;

class AssignFileAttachments extends \Magento\Backend\Block\Template
{
    //phpcs:ignore
    protected $_template = 'attachment/assign_file_attachments.phtml';

    /**
     * @var \MageSuite\FileAttachments\Block\Adminhtml\Attachment\Tab\Attachment
     */
    protected $blockGrid;

    protected \Magento\Catalog\Model\Locator\RegistryLocator $registryLocator;

    protected \MageSuite\FileAttachments\Model\ResourceModel\Attachment $resourceModel;

    protected \Magento\Framework\Serialize\SerializerInterface $serializer;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\Locator\RegistryLocator $registryLocator,
        \MageSuite\FileAttachments\Model\ResourceModel\Attachment $resourceModel,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        array $data = []
    ) {
        $this->registryLocator = $registryLocator;
        $this->resourceModel = $resourceModel;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                \MageSuite\FileAttachments\Block\Adminhtml\Attachment\Tab\Attachment::class,
                'file.attachments.grid'
            );
        }

        return $this->blockGrid;
    }

    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }

    public function getSelectedAttachmentsJson()
    {
        $productId = $this->getProduct()->getId();
        $attachmentIds = $this->resourceModel->getProductAttachmentIds($productId);

        return $this->serializer->serialize($attachmentIds);
    }

    public function getProduct()
    {
        return $this->registryLocator->getProduct();
    }
}
