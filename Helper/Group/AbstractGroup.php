<?php

namespace Ryvon\EventLog\Helper\Group;

use Ryvon\EventLog\Helper\Group\Template\DefaultTemplate;
use Ryvon\EventLog\Helper\Group\Template\TemplateInterface;
use Ryvon\EventLog\Model\EntryCollection;
use Magento\Backend\Model\UrlInterface;

/**
 * Abstract class for log groups that need customization.
 */
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
     * Called in constructor to allow plugins or sub-classes to override and call addHeadingLink.
     *
     * @return void
     */
    public function initialize()
    {
    }

    /**
     * @inheritdoc
     *
     * @return int
     */
    public function getSortOrder(): int
    {
        return static::SORT_ORDER;
    }

    /**
     * @inheritdoc
     *
     * @return TemplateInterface
     */
    public function getTemplate(): TemplateInterface
    {
        return new DefaultTemplate();
    }

    /**
     * @inheritdoc
     *
     * @param EntryCollection $entries
     * @return $this
     */
    public function setEntryCollection(EntryCollection $entries): GroupInterface
    {
        $this->entries = $entries;
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return EntryCollection
     */
    public function getEntryCollection(): EntryCollection
    {
        return $this->entries;
    }

    /**
     * Adds a link to the heading of the block.
     *
     * @param string $text
     * @param string $href
     * @return AbstractGroup
     */
    public function addHeadingLink(string $text, string $href): AbstractGroup
    {
        $this->headingLinks[$text] = $href;
        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return array
     */
    public function getHeadingLinks(): array
    {
        return $this->headingLinks;
    }

    /**
     * Helper function to retrieve the Url builder.
     *
     * @return UrlInterface
     */
    protected function getUrlBuilder(): UrlInterface
    {
        return $this->urlBuilder;
    }
}
