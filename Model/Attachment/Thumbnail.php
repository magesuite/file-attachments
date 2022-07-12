<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Model\Attachment;

class Thumbnail
{
    const IMAGE_FORMAT = 'jpg';

    protected \Magento\Framework\Filesystem\Driver\File $fileDriver;

    protected \Magento\Framework\Filesystem\Io\File $ioFile;

    protected \MageSuite\FileAttachments\Helper\Configuration $configuration;

    public function __construct(
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \MageSuite\FileAttachments\Helper\Configuration $configuration
    ) {
        $this->fileDriver = $fileDriver;
        $this->ioFile = $ioFile;
        $this->configuration = $configuration;
    }

    public function execute(\MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachment): void
    {
        $this->checkUploadFolder($attachment);
        $filePath = $attachment->getFilePath();
        $pathInfo = $this->ioFile->getPathInfo($filePath);

        if ($pathInfo['extension'] == 'pdf') {
            $filePath .= '[0]';
        }

        $imagick = new \Imagick($filePath);
        $imagick->setimageformat(self::IMAGE_FORMAT);
        $imagick->thumbnailimage($this->configuration->getThumbnailWidth(), 0);
        $imagick->writeimage($attachment->getThumbnailPath());
        $imagick->clear();
        $imagick->destroy();
    }

    protected function checkUploadFolder(\MageSuite\FileAttachments\Api\Data\AttachmentInterface $attachment): void
    {
        $thumbnailFolder = $this->fileDriver->getParentDirectory($attachment->getThumbnailPath());

        if ($this->fileDriver->isDirectory($thumbnailFolder)) {
            return;
        }

        $this->fileDriver->createDirectory($thumbnailFolder);
    }
}
