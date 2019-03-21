<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Ryvon\EventLog\Helper\SvgHelper;
use Magento\Backend\Model\UrlInterface;
use Magento\Cms\Helper\Page as PageHelper;
use Magento\Cms\Model\PageRepository;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;

class CmsPagePlaceholder implements PlaceholderInterface
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
     * @var SvgHelper
     */
    private $svgHelper;

    /**
     * @param UrlInterface $urlBuilder
     * @param PageHelper $pageHelper
     * @param PageRepository $pageRepository
     * @param SvgHelper $svgHelper
     */
    public function __construct(
        UrlInterface $urlBuilder,
        PageHelper $pageHelper,
        PageRepository $pageRepository,
        SvgHelper $svgHelper
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->pageHelper = $pageHelper;
        $this->pageRepository = $pageRepository;
        $this->svgHelper = $svgHelper;
    }

    /**
     * @return string
     */
    public function getSearchString(): string
    {
        return 'cms-page';
    }

    /**
     * @param DataObject $context
     * @return string|null
     */
    public function getReplaceString($context)
    {
        $pageName = $context->getData('cms-page');
        if (!$pageName) {
            return null;
        }

        $pageId = $context->getData('cms-page-id');
        if (!$pageId) {
            return $pageName;
        }

        try {
            $page = $this->pageRepository->getById($pageId);
        } catch (NoSuchEntityException $e) {
            return $pageName;
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
                'html' => $this->svgHelper->getStoreSvg(),
                'title' => 'View this page on the frontend',
                'href' => $frontendUrl,
                'target' => '_blank',
                'class' => 'icon',
            ]);
        }

        return $return;
    }
}
