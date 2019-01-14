<?php

namespace Ryvon\EventLog\Helper\Group;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Ryvon\EventLog\Helper\DigestSummarizer;
use Ryvon\EventLog\Helper\DuplicateCheckerFactory;
use Ryvon\EventLog\Model\Config;
use Ryvon\EventLog\Model\Entry;

abstract class AbstractLinksGroup extends AbstractGroup
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var string[]
     */
    private $headingLinks = [];

    /**
     * @param Config $config
     * @param DigestSummarizer $summarizer
     * @param DuplicateCheckerFactory $duplicateCheckerFactory
     * @param LayoutInterface $layout
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Config $config,
        DigestSummarizer $summarizer,
        DuplicateCheckerFactory $duplicateCheckerFactory,
        LayoutInterface $layout,
        UrlInterface $urlBuilder
    )
    {
        $this->urlBuilder = $urlBuilder;

        parent::__construct($config, $summarizer, $duplicateCheckerFactory, $layout);
    }

    /**
     * @return UrlInterface
     */
    protected function getUrlBuilder()
    {
        return $this->urlBuilder;
    }

    /**
     * @return void
     */
    public function initialize()
    {
    }

    /**
     * @param string $text
     * @param string $href
     * @return AbstractGroup
     */
    public function addHeadingLink($text, $href)
    {
        $this->headingLinks[$text] = $href;
        return $this;
    }

    /**
     * @return array
     */
    protected function getHeadingLinks()
    {
        return $this->headingLinks;
    }

    /**
     * @param Entry[] $entries
     * @param bool $hasUserColumn
     * @return string
     */
    protected function renderHeading($entries, $hasUserColumn)
    {
        return $this->createBlock(static::HEADER_BLOCK_CLASS)
            ->setTemplate(static::HEADER_TEMPLATE)
            ->addData([
                'title' => $this->getTitle(),
                'summary' => $this->getSummarizer()->buildSummaryMessage($entries),
                'links' => $this->renderLinks(),
                'user-column' => $hasUserColumn,
            ])
            ->toHtml();
    }

    /**
     * @return string
     */
    protected function renderLinks()
    {
        if (!count($this->getHeadingLinks())) {
            return '';
        }

        return $this->createBlock(\Magento\Backend\Block\Template::class)
            ->setTemplate('Ryvon_EventLog::heading/links.phtml')
            ->addData([
                'links' => $this->getHeadingLinks(),
            ])
            ->toHtml();
    }
}
