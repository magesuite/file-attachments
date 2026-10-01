<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\Api;

class GetAttachmentList
{
    public function __construct(
        protected \MageSuite\FileAttachments\Model\ResourceModel\Attachment\CollectionFactory $attachmentCollectionFactory,
        protected \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor,
        protected \MageSuite\FileAttachments\Api\Data\ApiAttachmentSearchResultsInterfaceFactory $searchResultsFactory
    ) {
    }

    public function execute(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ): \MageSuite\FileAttachments\Api\Data\ApiAttachmentSearchResultsInterface {
        $attachmentCollection = $this->attachmentCollectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $attachmentCollection);
        $attachmentCollection->addStoreIdsToResults();
        $attachmentCollection->addCustomerGroupIdsToResults();
        $attachmentCollection->addProductSkusToResults();

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems(array_values($attachmentCollection->getItems()));
        $searchResults->setTotalCount($attachmentCollection->getSize());

        return $searchResults;
    }
}
