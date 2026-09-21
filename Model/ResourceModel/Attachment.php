<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\ResourceModel;

class Attachment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public const ASSIGNMENT_BATCH_ROWS = 50000;

    protected string $storeTable;

    protected string $productTable;

    protected function _construct(): void
    {
        $this->_init('file_attachments', 'attachment_id');

        $this->storeTable = $this->getTable('file_attachments_store');
        $this->productTable = $this->getTable('file_attachments_product');
    }

    public function getProductAttachmentIds(mixed $productId): array
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->productTable, ['attachment_id'])
            ->where('product_id = ?', $productId);

        return $this->getConnection()->fetchCol($select);
    }

    public function assignAttachmentsToProduct(int $productId, array $attachmentIds): self
    {
        $connection = $this->getConnection();
        $oldAttachments = $this->getProductAttachmentIds($productId);
        $delete = array_diff($oldAttachments, $attachmentIds);
        $insert = array_diff($attachmentIds, $oldAttachments);

        if ($delete) {
            $where = [
                'product_id = ?' => $productId,
                'attachment_id IN (?)' => $delete,
            ];
            $connection->delete($this->productTable, $where);
        }

        if ($insert) {
            $data = [];

            foreach ($insert as $attachmentId) {
                $data[] = [
                    'product_id' => $productId,
                    'attachment_id' => (int)$attachmentId,
                ];
            }

            $connection->insertMultiple($this->productTable, $data);
        }

        return $this;
    }

    public function assignStoresToAttachments(array $attachmentIds, array $storeIds, bool $replaceExisting): void
    {
        if (empty($attachmentIds) || empty($storeIds)) {
            return;
        }

        $batches = array_chunk($attachmentIds, $this->getAttachmentBatchSize($storeIds));
        foreach ($batches as $attachmentIdsBatch) {
            if ($replaceExisting) {
                $this->deleteStoreAssignments($attachmentIdsBatch);
            }

            $this->insertStoreAssignments($attachmentIdsBatch, $storeIds);
        }
    }

    protected function getAttachmentBatchSize(array $storeIds): int
    {
        return max(1, intdiv(self::ASSIGNMENT_BATCH_ROWS, count($storeIds)));
    }

    protected function deleteStoreAssignments(array $attachmentIds): void
    {
        $this->getConnection()->delete($this->storeTable, ['attachment_id IN (?)' => $attachmentIds]);
    }

    protected function insertStoreAssignments(array $attachmentIds, array $storeIds): void
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['fa' => $this->getMainTable()], ['attachment_id'])
            ->joinCross(['s' => $this->getTable('store')], ['store_id'])
            ->where('fa.attachment_id IN (?)', $attachmentIds)
            ->where('s.store_id IN (?)', $storeIds);

        $connection->query(
            $connection->insertFromSelect(
                $select,
                $this->storeTable,
                ['attachment_id', 'store_id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INSERT_ON_DUPLICATE
            )
        );
    }

    public function lookupStoreIds(mixed $id): array
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(['fas' => $this->storeTable], 'store_id')
            ->join(
                ['fa' => $this->getMainTable()],
                'fas.attachment_id = fa.attachment_id',
                []
            )
            ->where('fa.attachment_id = :attachment_id');

        return $connection->fetchCol($select, ['attachment_id' => (int)$id]);
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object): self
    {
        $connection = $this->getConnection();
        $oldStores = $this->lookupStoreIds((int)$object->getId());
        $newStores = (array)$object->getStoreIds();
        $delete = array_diff($oldStores, $newStores);
        $insert = array_diff($newStores, $oldStores);

        if ($delete) {
            $where = [
                'attachment_id = ?' => (int)$object->getId(),
                'store_id IN (?)' => $delete,
            ];
            $connection->delete($this->storeTable, $where);
        }

        if ($insert) {
            $data = [];

            foreach ($insert as $storeId) {
                $data[] = [
                    'attachment_id' => (int)$object->getId(),
                    'store_id' => (int)$storeId,
                ];
            }

            $connection->insertMultiple($this->storeTable, $data);
        }

        return parent::_afterSave($object);
    }

    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object): self
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds((int)$object->getId());
            $object->setData('store_ids', $stores);
        }

        return parent::_afterLoad($object);
    }
}
