<?php

namespace Ryvon\EventLog\Helper\Group;

use Ryvon\EventLog\Helper\Group\Template\DefaultTemplate;
use Ryvon\EventLog\Helper\Group\Template\TemplateInterface;
use Ryvon\EventLog\Model\EntryCollection;
use Magento\Backend\Model\UrlInterface;

abstract class AbstractGroup implements GroupInterface
{
    /**
     * @var int
     */
    const SORT_ORDER = 40;

    /**
     * @var EntryCollection
     */
    private $entries = [];

    /**
     * @var string[]
     */
    private $headingLinks = [];

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     */
    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;

        $this->initialize();
    }

    /**
     * @return void
     */
    public function initialize()
    {
    }

    /**
     * @return UrlInterface
     */
    protected function getUrlBuilder(): UrlInterface
    {
        return $this->urlBuilder;
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return static::SORT_ORDER;
    }

    /**
     * @return TemplateInterface
     */
    public function getTemplate(): TemplateInterface
    {
        return new DefaultTemplate();
    }

    /**
     * @param EntryCollection $entries
     * @return $this
     */
    public function setEntryCollection(EntryCollection $entries): GroupInterface
    {
        $this->entries = $entries;
        return $this;
    }

    /**
     * @return EntryCollection
     */
    public function getEntryCollection(): EntryCollection
    {
        return $this->entries;
    }

    /**
     * @param string $text
     * @param string $href
     * @return AbstractGroup
     */
    public function addHeadingLink($text, $href): AbstractGroup
    {
        $this->headingLinks[$text] = $href;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeadingLinks(): array
    {
        return $this->headingLinks;
    }
}
