<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\Attachment;

enum StoreAssignmentMode: string
{
    case ADD = 'add';
    case REPLACE = 'replace';
}
