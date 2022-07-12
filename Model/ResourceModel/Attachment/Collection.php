<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\ResourceModel\Attachment;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->storeManager = $storeManager;
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

    public function addStoreFilter(): self
    {
        $storeIds = [
            \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            $this->storeManager->getStore()->getId()
        ];
        $this->getSelect()
            ->join(
                ['fas' => $this->getTable('file_attachments_store')],
                'main_table.attachment_id = fas.attachment_id',
                []
            )
            ->where('fas.store_id IN (?)', implode(',', $storeIds));

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
}
