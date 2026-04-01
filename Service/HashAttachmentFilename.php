<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Service;

class HashAttachmentFilename
{
    public function getHashFromFilename(string $filename): string
    {
        return hash('sha256', $filename);
    }
}
