<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\ResourceModel\Attachment;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    protected function _construct(): void
    {
        $this->_init(\MageSuite\FileAttachments\Model\Attachment::class, \MageSuite\FileAttachments\Model\ResourceModel\Attachment::class);
    }

    public function addStoreIdsToResults(): self
    {
        $linkedIds = $this->getAllIds();

        if (empty($linkedIds)) {
            return $this;
        }

        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('file_attachments_store'))
            ->where('attachment_id IN (?)', $linkedIds);
        $result = $connection->fetchAll($select);

        if (!$result) {
            return $this;
        }

        $storesData = [];

        foreach ($result as $storeData) {
            $storesData[$storeData['attachment_id']][] = $storeData['store_id'];
        }

        foreach ($this as $item) {
            $linkedId = $item->getId();

            if (!isset($storesData[$linkedId])) {
                continue;
            }

            $item->setData('store_ids', $storesData[$linkedId]);
        }

        return $this;
    }

    public function addCustomerGroupIdsToResults(): self
    {
        $linkedIds = $this->getAllIds();

        if (empty($linkedIds)) {
            return $this;
        }

        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('file_attachments_customer_group'))
            ->where('attachment_id IN (?)', $linkedIds);
        $result = $connection->fetchAll($select);

        $customerGroupsData = [];

        if ($result) {
            foreach ($result as $customerGroupData) {
                $customerGroupsData[$customerGroupData['attachment_id']][] = $customerGroupData['customer_group_id'];
            }
        }

        $this->addCustomerGroupsForMissingLinkedIds($linkedIds, $customerGroupsData);

        foreach ($this as $item) {
            $linkedId = $item->getId();

            if (!isset($customerGroupsData[$linkedId])) {
                continue;
            }

            $item->setData('customer_group_ids', $customerGroupsData[$linkedId]);
        }

        return $this;
    }

    public function addStoreFilter(array $storeIds): self
    {
        $this->getSelect()
            ->join(
                ['fas' => $this->getTable('file_attachments_store')],
                'main_table.attachment_id = fas.attachment_id',
                []
            )
            ->where('fas.store_id IN (?)', $storeIds);

        return $this;
    }

    public function addCustomerGroupFilter(string|int $customerGroupId): self
    {
        $customerGroupAttachments = $this->getConnection()
            ->select()
            ->from(['facg' => $this->getTable('file_attachments_customer_group')], 'customer_group_id')
            ->where('facg.customer_group_id = ?', $customerGroupId)
            ->query()
            ->fetchAll();

        if (empty($customerGroupAttachments)) {
            return $this;
        }

        $this->getSelect()
            ->join(
                ['facg' => $this->getTable('file_attachments_customer_group')],
                'main_table.attachment_id = facg.attachment_id',
                []
            )
            ->where('facg.customer_group_id = ?', $customerGroupId);

        return $this;
    }

    public function joinProductTable(): self
    {
        $this->getSelect()
            ->joinLeft(
                ['fap' => $this->getTable('file_attachments_product')],
                'main_table.attachment_id = fap.attachment_id',
                []
            )->group('main_table.attachment_id');

        return $this;
    }

    public function sortByOrder(): self
    {
        $this->getSelect()->order('main_table.sort_order ASC');

        return $this;
    }

    protected function getAllCustomerGroups(): array
    {
        return $this->getConnection()->select()
            ->from($this->getTable('customer_group'), ['customer_group_id'])
            ->query()
            ->fetchAll();
    }

    protected function addCustomerGroupsForMissingLinkedIds(array $linkedIds, array &$customerGroupsData): void
    {
        foreach ($linkedIds as $linkedId) {
            if (isset($customerGroupsData[$linkedId])) {
                continue;
            }

            foreach ($this->getAllCustomerGroups() as $customerGroup) {
                $customerGroupsData[$linkedId][] = $customerGroup['customer_group_id'];
            }
        }
    }
}
