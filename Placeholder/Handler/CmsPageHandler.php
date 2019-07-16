<?php

namespace Ryvon\EventLog\Placeholder\Handler;

use Magento\Backend\Model\UrlInterface;
use Magento\Cms\Helper\Page as PageHelper;
use Magento\Cms\Model\PageRepository;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Ryvon\EventLog\Helper\ImageLocator;

class CmsPageHandler implements HandlerInterface
{
    use LinkPlaceholderTrait;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var PageHelper
     */
    private $pageHelper;

    /**
     * @var PageRepository
     */
    private $pageRepository;

    /**
     * @var ImageLocator
     */
    private $imageLocator;

    /**
     * @param UrlInterface $urlBuilder
     * @param PageHelper $pageHelper
     * @param PageRepository $pageRepository
     * @param ImageLocator $imageLocator
     */
    public function __construct(
        UrlInterface $urlBuilder,
        PageHelper $pageHelper,
        PageRepository $pageRepository,
        ImageLocator $imageLocator
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->pageHelper = $pageHelper;
        $this->pageRepository = $pageRepository;
        $this->imageLocator = $imageLocator;
    }

    /**
     * @inheritDoc
     */
    public function handle(DataObject $context)
    {
        $pageId = $context->getData('id');
        $pageName = $context->getData('text');
        if (!$pageId || !$pageName) {
            return null;
        }

        try {
            $page = $this->pageRepository->getById($pageId);
        } catch (NoSuchEntityException $e) {
            return null;
        }

        $return = $this->buildLinkTag([
            'text' => $pageName,
            'title' => 'Edit this page in the admin',
            'href' => $this->urlBuilder->getUrl('cms/page/edit', [
                'page_id' => $pageId,
            ]),
            'target' => '_blank',
        ]);

        $frontendUrl = $this->pageHelper->getPageUrl($pageId);
        if ($frontendUrl && $page->isActive()) {
            $return .= $this->buildLinkTag([
                'html' => $this->imageLocator->getIconSvg('store') ?: '[Frontend]',
                'title' => 'View this page on the frontend',
                'href' => $frontendUrl,
                'target' => '_blank',
                'class' => 'icon',
            ]);
        }

        return $return;
    }
}
