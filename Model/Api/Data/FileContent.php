<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\Api\Data;

class FileContent extends \Magento\Framework\DataObject implements \MageSuite\FileAttachments\Api\Data\FileContentInterface
{
    public function getFileData(): string
    {
        return (string)$this->_getData(self::FILE_DATA);
    }

    public function setFileData(string $fileData): self
    {
        return $this->setData(self::FILE_DATA, $fileData);
    }

    public function getName(): string
    {
        return (string)$this->_getData(self::NAME);
    }

    public function setName(string $name): self
    {
        return $this->setData(self::NAME, $name);
    }
}
