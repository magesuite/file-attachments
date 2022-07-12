<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Helper;

class Configuration
{
    public const XML_PATH_GENERAL_THUMBNAIL_WIDTH = 'file_attachments/general/thumbnail_width';

    protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getThumbnailWidth(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_GENERAL_THUMBNAIL_WIDTH);
    }
}
