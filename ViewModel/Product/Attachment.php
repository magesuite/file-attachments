<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\ViewModel\Product;

class Attachment implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    protected \Magento\Framework\Registry $registry;

    protected \MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository;

    protected \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder;

    protected \Magento\Framework\Api\FilterBuilder $filterBuilder;

    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->registry = $registry;
        $this->attachmentRepository = $attachmentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->storeManager = $storeManager;
    }

    public function getAttachments()
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilters(
            [
                $this->filterBuilder->setField('product_id')
                    ->setValue($this->getProduct()->getId())
                    ->create()
            ]
        )->create();

        return $this->attachmentRepository->getList($searchCriteria)->getItems();
    }

    public function getProduct(): ?\Magento\Catalog\Model\Product
    {
        return $this->registry->registry('current_product');
    }
}
