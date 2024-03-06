<?php

namespace MageSuite\FileAttachments\Model\Config\Source;

class CustomerGroups implements \Magento\Framework\Option\ArrayInterface
{
    protected $options;

    protected \Magento\Customer\Api\GroupRepositoryInterface $groupRepository;
    protected \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder;
    protected \Magento\Framework\Convert\DataObject $objectConverter;

    public function __construct(
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter
    ) {
        $this->groupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectConverter = $objectConverter;
    }

    public function toOptionArray()
    {
        if (!$this->options) {
            $groups = $this->groupRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            $this->options = $this->objectConverter->toOptionArray($groups, 'id', 'code');
        }

        return $this->options;
    }
}
