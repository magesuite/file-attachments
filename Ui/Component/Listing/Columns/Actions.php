<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Ui\Component\Listing\Columns;

class Actions extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $name = $this->getData('name');
            $item[$name]['edit'] = [
                'href' => $this->context->getUrl('fileattachments/attachment/edit', [
                    'attachment_id' => $item[$item['id_field_name']]
                ]),
                'label' => __('Edit'),
            ];
            $item[$name]['delete'] = [
                'href' => $this->context->getUrl('fileattachments/attachment/delete', [
                    'attachment_id' => $item[$item['id_field_name']]
                ]),
                'label' => __('Delete'),
                'confirm' => [
                    'title' => __('Delete %1', $name),
                    'message' => __('Are you sure you want to delete a %1 record?', $name),
                ],
                'post' => true
            ];
        }

        return $dataSource;
    }
}
