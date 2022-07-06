<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Block\Adminhtml\Attachment\Tab;

class Attachment extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected \MageSuite\FileAttachments\Model\ResourceModel\Attachment\CollectionFactory $collectionFactory;

    protected \MageSuite\FileAttachments\Model\ResourceModel\Attachment $resourceModel;

    protected \Magento\Catalog\Model\Locator\RegistryLocator $registryLocator;

    protected \Magento\Framework\Serialize\SerializerInterface $serializer;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MageSuite\FileAttachments\Model\ResourceModel\Attachment\CollectionFactory $collectionFactory,
        \MageSuite\FileAttachments\Model\ResourceModel\Attachment $resourceModel,
        \Magento\Catalog\Model\Locator\RegistryLocator $registryLocator,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->resourceModel = $resourceModel;
        $this->registryLocator = $registryLocator;
        $this->serializer = $serializer;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('catalog_product_file_attachments');
        $this->setDefaultSort('attachment_id');
        $this->setUseAjax(true);
    }

    public function getProduct()
    {
        return $this->registryLocator->getProduct();
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'is_selected') {
            $attachmentIds = $this->_getSelectedFileAttachments();
            if (empty($attachmentIds)) {
                $attachmentIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('attachment_id', ['in' =>  $attachmentIds]);
            } elseif (!empty($attachmentIds)) {
                $this->getCollection()->addFieldToFilter('attachment_id', ['nin' =>  $attachmentIds]);
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    protected function _prepareCollection()
    {
        $collection = $this->collectionFactory->create();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'is_selected',
            [
                'type' => 'checkbox',
                'name' => 'attachment_id',
                'inline_css' => 'checkbox entities',
                'field_name' => 'product[attachment_ids][]',
                'values' => $this->_getSelectedFileAttachments(),
                'index' => 'attachment_id',
                'align' => 'center'
            ]
        );
        $this->addColumn(
            'attachment_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'attachment_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'width' => '160'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'index' => 'name'
            ]
        );
        $this->addColumn(
            'filename',
            [
                'header' => __('Filename'),
                'index' => 'filename'
            ]
        );
        $this->addColumn(
            'preview',
            [
                'header' => __('Preview'),
                'renderer' => \MageSuite\FileAttachments\Block\Adminhtml\Attachment\Grid\Column\Renderer\Preview::class,
            ]
        );

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('fileattachments/attachment/grid', ['_current' => true]);
    }

    protected function _getSelectedFileAttachments()
    {
        $fileAttachments = $this->getRequest()->getPost('selected_file_attachments');

        if ($fileAttachments === null) {
            $productId = $this->getProduct()->getId();
            $fileAttachments = $this->resourceModel->getProductAttachmentIds($productId);
        }

        return $fileAttachments;
    }
}
