<?php
declare(strict_types=1);

namespace MageSuite\FileAttachments\Block\Adminhtml\Attachment\Edit;

class GenericButton
{
    protected \Magento\Backend\Block\Widget\Context $context;

    protected \Magento\Framework\UrlInterface $urlBuilder;

    protected \Magento\Framework\Registry $registry;

    protected \MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository;

    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \MageSuite\FileAttachments\Api\AttachmentRepositoryInterface $attachmentRepository
    ) {
        $this->context = $context;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->registry = $registry;
        $this->attachmentRepository = $attachmentRepository;
    }

    public function getUrl($route = '', $params = []): string
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    public function getAttachmentId(): ?int
    {
        $id = (int)$this->context->getRequest()->getParam('attachment_id');

        try {
            return (int)$this->attachmentRepository->getById($id)->getId();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) { //phpcs:ignore
        }

        return null;
    }
}
