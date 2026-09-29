<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Api\Data;

interface ApiAttachmentSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * @return \MageSuite\FileAttachments\Api\Data\AttachmentInterface[]
     */
    public function getItems();

    /**
     * @param \MageSuite\FileAttachments\Api\Data\AttachmentInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
