<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Backend\Model\UrlInterface;
use Magento\Cms\Helper\Page;
use Magento\Framework\DataObject;
use Ryvon\EventLog\Helper\SvgHelper;

class CmsPagePlaceholder implements PlaceholderInterface
{
    use LinkPlaceholderTrait;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Page
     */
    private $pageHelper;

    /**
     * @var SvgHelper
     */
    private $svgHelper;

    /**
     * @param UrlInterface $urlBuilder
     * @param Page $pageHelper
     * @param SvgHelper $svgHelper
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Page $pageHelper,
        SvgHelper $svgHelper
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->pageHelper = $pageHelper;
        $this->svgHelper = $svgHelper;
    }

    /**
     * @return string
     */
    public function getSearchString()
    {
        return 'cms-page';
    }

    /**
     * @param DataObject $context
     * @return string
     */
    public function getReplaceString($context)
    {
        $pageName = $context->getData('cms-page');
        if (!$pageName) {
            return false;
        }

        $pageId = $context->getData('cms-page-id');
        if (!$pageId) {
            return $pageName;
        }

        $frontendUrl = $this->pageHelper->getPageUrl($pageId);
        if (!$frontendUrl) {
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

        $return .= $this->buildLinkTag([
            'html' => $this->svgHelper->getStoreSvg(),
            'title' => 'View this page on the frontend',
            'href' => $frontendUrl,
            'target' => '_blank',
            'class' => 'icon',
        ]);

        return $return;
    }
}
