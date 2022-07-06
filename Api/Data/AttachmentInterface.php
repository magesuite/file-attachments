<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Api\Data;

interface AttachmentInterface
{
    public const ATTACHMENT_ID = 'attachment_id';
    public const FILENAME = 'filename';
    public const NAME = 'name';
    public const DESCRIPTION = 'description';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * @return int
     */
    public function getAttachmentId();

    /**
     * @param int $attachmentId
     * @return AttachmentInterface
     */
    public function setAttachmentId(int $attachmentId);

    /**
     * @return string
     */
    public function getFilename();

    /**
     * @param string $filename
     * @return AttachmentInterface
     */
    public function setFilename(string $filename);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return AttachmentInterface
     */
    public function setName(string $name);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $description
     * @return AttachmentInterface
     */
    public function setDescription(string $description);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     * @return AttachmentInterface
     */
    public function setCreatedAt(string $createdAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $updatedAt
     * @return AttachmentInterface
     */
    public function setUpdatedAt(string $updatedAt);

    /**
     * @return string
     */
    public function getFilePath();

    /**
     * @return string
     */
    public function getFileUrl();
}
