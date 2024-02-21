<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Ui\DataProvider;

class AttachmentDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $collection;

    protected \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor;

    protected \Magento\Framework\Filesystem\Driver\File $file;

    protected array $loadedData = [];

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \MageSuite\FileAttachments\Model\ResourceModel\Attachment\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Framework\Filesystem\Driver\File $file,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->file = $file;
    }

    public function getData(): ?array
    {
        if (!empty($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->getCollection()
            ->addStoreIdsToResults()
            ->addCustomerGroupIdsToResults()
            ->getItems();

        foreach ($items as $attachment) {
            $attachmentData = $attachment->getData();
            $this->adjustFileData($attachment, $attachmentData);
            $this->loadedData[$attachment->getId()] = $attachmentData;
        }

        $data = $this->dataPersistor->get('fileattachments_attachment_attachment');

        if (!empty($data)) {
            $attachment = $this->getCollection()->getNewEmptyItem();
            $attachment->setData($data);
            $this->loadedData[$attachment->getId()] = $attachment->getData();
            $this->dataPersistor->clear('fileattachments_attachment_attachment');
        }

        return $this->loadedData;
    }

    protected function adjustFileData(\MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachment, array &$attachmentData): void
    {
        if (!isset($attachmentData[$attachment::FILENAME])) {
            return;
        }

        $name = $attachmentData[$attachment::FILENAME];
        unset($attachmentData[$attachment::FILENAME]);
        $attachmentData[$attachment::FILENAME][0] = [
            'name' => $name,
            'url' => $attachment->getFileUrl(),
            'size' => $this->file->isFile($attachment->getFilePath()) ? $this->file->stat($attachment->getFilePath())['size'] : 0
        ];
    }
}
