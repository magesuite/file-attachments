<?php

declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\Api\Validators;

class FileContentValidator
{
    protected const FILENAME_PATTERN = '/^[^\\/?*:";<>()|{}\\\\]+$/';

    public function validate(\MageSuite\FileAttachments\Api\Data\FileContentInterface $fileContent): void
    {
        $decodedContent = base64_decode($fileContent->getFileData(), true);

        if (empty($decodedContent)) {
            throw new \Magento\Framework\Exception\InputException(
                __('Provided file content must be valid base64 encoded data.')
            );
        }

        if (!$this->isFileNameValid($fileContent->getName())) {
            throw new \Magento\Framework\Exception\InputException(
                __('Provided file name is empty or contains forbidden characters.')
            );
        }
    }

    protected function isFileNameValid(string $fileName): bool
    {
        return $fileName !== '' && preg_match(self::FILENAME_PATTERN, $fileName) === 1;
    }
}
