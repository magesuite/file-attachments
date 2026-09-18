<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\ResourceModel\Attachment\Grid;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    public const STORE_FIELD = 'store_id';
    public const STORE_TABLE_ALIAS = 'store_table';

    protected bool $storeTableJoined = false;

    public function addFieldToFilter(mixed $field, mixed $condition = null): self
    {
        if ($field !== self::STORE_FIELD) {
            return parent::addFieldToFilter($field, $condition);
        }

        $this->joinStoreTable();

        return parent::addFieldToFilter(sprintf('%s.%s', self::STORE_TABLE_ALIAS, self::STORE_FIELD), $condition);
    }

    protected function joinStoreTable(): void
    {
        if ($this->storeTableJoined) {
            return;
        }

        $this->getSelect()
            ->join(
                [self::STORE_TABLE_ALIAS => $this->getTable('file_attachments_store')],
                sprintf('main_table.attachment_id = %s.attachment_id', self::STORE_TABLE_ALIAS),
                []
            )
            ->group('main_table.attachment_id');

        $this->storeTableJoined = true;
    }

    protected function _afterLoad(): self
    {
        $this->addStoreIdsToItems();

        return parent::_afterLoad();
    }

    protected function addStoreIdsToItems(): void
    {
        $attachmentIds = $this->getColumnValues('attachment_id');
        if (empty($attachmentIds)) {
            return;
        }

        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('file_attachments_store'), ['attachment_id', self::STORE_FIELD])
            ->where('attachment_id IN (?)', $attachmentIds);
        $storeIds = [];

        foreach ($connection->fetchAll($select) as $row) {
            $storeIds[$row['attachment_id']][] = $row[self::STORE_FIELD];
        }

        foreach ($this->getItems() as $item) {
            $attachmentId = $item->getData('attachment_id');
            $item->setData(self::STORE_FIELD, $this->normalizeStoreIds($storeIds[$attachmentId] ?? []));
        }
    }

    protected function normalizeStoreIds(array $storeIds): array
    {
        $storeIds = array_map('intval', $storeIds);

        if (in_array(\Magento\Store\Model\Store::DEFAULT_STORE_ID, $storeIds, true)) {
            return [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
        }

        return $storeIds;
    }
}
