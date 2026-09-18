<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Ui\DataProvider;

class AssignStoresDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    public function getData(): array
    {
        return [];
    }

    /**
     * The form only collects the store views to assign, it is not backed by an entity collection to filter.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    public function addFilter(\Magento\Framework\Api\Filter $filter): void
    {
    }
}
