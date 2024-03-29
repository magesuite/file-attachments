<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Model;

class AttachmentRepository implements \MageSuite\FileAttachments\Api\AttachmentRepositoryInterface
{
    protected \MageSuite\FileAttachments\Model\AttachmentFactory $attachmentFactory;

    protected \MageSuite\FileAttachments\Model\ResourceModel\Attachment $attachmentResource;

    protected \MageSuite\FileAttachments\Model\ResourceModel\Attachment\CollectionFactory $collectionFactory;

    protected \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory;

    protected \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor;

    protected \Psr\Log\LoggerInterface $logger;

    protected array $instancesById = [];

    public function __construct(
        \MageSuite\FileAttachments\Model\AttachmentFactory $attachmentFactory,
        \MageSuite\FileAttachments\Model\ResourceModel\Attachment $attachmentResource,
        \MageSuite\FileAttachments\Model\ResourceModel\Attachment\CollectionFactory $collectionFactory,
        \Magento\Framework\Api\SearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->attachmentFactory = $attachmentFactory;
        $this->attachmentResource = $attachmentResource;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->logger = $logger;
    }

    public function getById($attachmentId)
    {
        if (!isset($this->instancesById[$attachmentId])) {
            $attachment = $this->attachmentFactory->create();
            $attachment->load($attachmentId);
            $this->instancesById[$attachmentId] = $attachment;
        }

        if (!$this->instancesById[$attachmentId]->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('The attachment with the "%1" ID doesn\'t exist.', $attachmentId)
            );
        }

        return $this->instancesById[$attachmentId];
    }

    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $collection = $this->collectionFactory->create();
        $collection->addStoreFilter();
        $collection->joinProductTable();
        $collection->sortByOrder();
        $this->collectionProcessor->process($criteria, $collection);

        foreach ($collection->getItems() as $attachment) {
            $this->instancesById[$attachment->getId()] = $attachment;
        }

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->count());

        return $searchResults;
    }

    public function save(\MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachment)
    {
        try {
            $this->attachmentResource->save($attachment);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Could not save the attachment: %1', $exception->getMessage()),
                $exception
            );
        }

        return $attachment;
    }

    public function delete(\MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachment)
    {
        try {
            $this->attachmentResource->delete($attachment);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Could not delete the attachment: %1', $exception->getMessage())
            );
        }

        return true;
    }

    public function deleteById($attachmentId)
    {
        return $this->delete($this->getById($attachmentId));
    }
}
