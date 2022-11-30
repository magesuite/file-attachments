<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Ui\Component\Listing\Columns;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected \Magento\Framework\UrlInterface $urlBuilder;

    protected \MageSuite\FileAttachments\Api\Data\AttachmentInterfaceFactory $attachmentFactory;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \MageSuite\FileAttachments\Api\Data\AttachmentInterfaceFactory $attachmentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->attachmentFactory = $attachmentFactory;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');
        $attachment = $this->attachmentFactory->create();

        foreach ($dataSource['data']['items'] as &$item) {
            $attachment->setData($item);
            $item[$fieldName . '_src'] = $attachment->getThumbnailUrl();
            $item[$fieldName . '_alt'] = '';
            $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                'fileattachments/attachment/edit',
                [$fieldName => $item['attachment_id']]
            );
            $item[$fieldName . '_orig_src'] = $attachment->getThumbnailUrl();
        }

        return $dataSource;
    }
}
