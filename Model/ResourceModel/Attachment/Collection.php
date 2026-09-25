<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\ResourceModel\Attachment;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected const array VIRTUAL_FILTER_FIELDS = ['store_id', 'customer_group_id', 'product_sku'];

    protected const array VIRTUAL_FILTER_CONDITION_TYPES = ['eq', 'in'];

    protected function _construct(): void
    {
        $this->_init(\MageSuite\FileAttachments\Model\Attachment::class, \MageSuite\FileAttachments\Model\ResourceModel\Attachment::class);
    }

    protected function _translateCondition($field, $condition) //phpcs:ignore
    {
        if (!in_array($field, self::VIRTUAL_FILTER_FIELDS, true)) {
            return parent::_translateCondition($field, $condition);
        }

        $values = $this->getVirtualFilterValues($field, $condition);

        return match ($field) {
            'store_id' => $this->getStoreIdCondition($values),
            'customer_group_id' => $this->getCustomerGroupIdCondition($values),
            'product_sku' => $this->getProductSkuCondition($values),
        };
    }

    protected function getVirtualFilterValues(string $field, mixed $condition): array
    {
        $conditionType = is_array($condition) ? (string)key($condition) : 'eq';

        if (!in_array($conditionType, self::VIRTUAL_FILTER_CONDITION_TYPES, true)) {
            throw new \Magento\Framework\Exception\InputException(
                __('Condition type "%1" is not supported for "%2".', $conditionType, $field)
            );
        }

        $value = is_array($condition) ? current($condition) : $condition;

        return array_values(array_unique(array_map('strval', $this->getFilterValueList($value, $conditionType))));
    }

    protected function getFilterValueList(mixed $value, string $conditionType): array
    {
        if (is_array($value)) {
            return $value;
        }

        if ($conditionType === 'in') {
            return array_map('trim', explode(',', (string)$value));
        }

        return [(string)$value];
    }

    protected function getStoreIdCondition(array $storeIds): string
    {
        $storeIds = array_map('intval', $storeIds);
        $storeIds[] = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

        $attachmentIdsSelect = $this->getConnection()->select()
            ->from($this->getTable('file_attachments_store'), ['attachment_id'])
            ->where('store_id IN (?)', array_values(array_unique($storeIds)));

        return sprintf('main_table.attachment_id IN (%s)', $attachmentIdsSelect);
    }

    protected function getCustomerGroupIdCondition(array $customerGroupIds): string
    {
        $customerGroupTable = $this->getTable('file_attachments_customer_group');
        $assignedAttachmentIdsSelect = $this->getConnection()->select()
            ->from($customerGroupTable, ['attachment_id'])
            ->where('customer_group_id IN (?)', array_map('intval', $customerGroupIds));
        $attachmentIdsWithAnyGroupSelect = $this->getConnection()->select()
            ->from($customerGroupTable, ['attachment_id']);

        return sprintf(
            '(main_table.attachment_id IN (%s) OR main_table.attachment_id NOT IN (%s))',
            $assignedAttachmentIdsSelect,
            $attachmentIdsWithAnyGroupSelect
        );
    }

    protected function getProductSkuCondition(array $productSkus): string
    {
        $attachmentIdsSelect = $this->getConnection()->select()
            ->from(['fap' => $this->getTable('file_attachments_product')], ['attachment_id'])
            ->join(
                ['cpe' => $this->getTable('catalog_product_entity')],
                'fap.product_id = cpe.entity_id',
                []
            )
            ->where('cpe.sku IN (?)', $productSkus);

        return sprintf('main_table.attachment_id IN (%s)', $attachmentIdsSelect);
    }

    public function addStoreIdsToResults(): self
    {
        $linkedIds = $this->getLoadedAttachmentIds();

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
        $linkedIds = $this->getLoadedAttachmentIds();

        if (empty($linkedIds)) {
            return $this;
        }

        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('file_attachments_customer_group'))
            ->where('attachment_id IN (?)', $linkedIds);
        $customerGroupsData = [];

        foreach ($connection->fetchAll($select) as $customerGroupData) {
            $customerGroupsData[$customerGroupData['attachment_id']][] = $customerGroupData['customer_group_id'];
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

    public function addProductSkusToResults(): self
    {
        $attachmentIds = $this->getLoadedAttachmentIds();

        if (empty($attachmentIds)) {
            return $this;
        }

        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['fap' => $this->getTable('file_attachments_product')], ['attachment_id'])
            ->join(
                ['cpe' => $this->getTable('catalog_product_entity')],
                'fap.product_id = cpe.entity_id',
                ['sku']
            )
            ->where('fap.attachment_id IN (?)', $attachmentIds)
            ->order('cpe.sku ASC');

        $productSkus = [];

        foreach ($connection->fetchAll($select) as $row) {
            $productSkus[$row['attachment_id']][] = $row['sku'];
        }

        foreach ($this as $item) {
            $item->setData('product_skus', $productSkus[$item->getId()] ?? []);
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

    protected function getLoadedAttachmentIds(): array
    {
        return $this->getColumnValues('attachment_id');
    }

    protected function getAllCustomerGroupIds(): array
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('customer_group'), ['customer_group_id'])
            ->order('customer_group_id');

        return $this->getConnection()->fetchCol($select);
    }

    protected function addCustomerGroupsForMissingLinkedIds(array $linkedIds, array &$customerGroupsData): void
    {
        $missingLinkedIds = array_diff($linkedIds, array_keys($customerGroupsData));

        if (empty($missingLinkedIds)) {
            return;
        }

        $allCustomerGroupIds = $this->getAllCustomerGroupIds();

        foreach ($missingLinkedIds as $linkedId) {
            $customerGroupsData[$linkedId] = $allCustomerGroupIds;
        }
    }
}
