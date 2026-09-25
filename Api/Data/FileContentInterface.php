<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Api\Data;

interface FileContentInterface
{
    public const FILE_DATA = 'file_data';
    public const NAME = 'name';

    /**
     * @return string
     */
    public function getFileData(): string;

    /**
     * @param string $fileData
     * @return $this
     */
    public function setFileData(string $fileData): self;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name): self;
}
