<?php

namespace Ryvon\EventLog\Helper\Group;

use Magento\Framework\View\LayoutInterface;
use Ryvon\EventLog\Helper\DigestSummarizer;
use Ryvon\EventLog\Helper\DuplicateChecker;
use Ryvon\EventLog\Helper\DuplicateCheckerFactory;
use Ryvon\EventLog\Model\Config;
use Ryvon\EventLog\Model\Entry;

abstract class AbstractGroup implements GroupInterface
{
    /**
     * @var string
     */
    const GROUP_ID = false;

    /**
     * @var string
     */
    const GROUP_TEMPLATE = 'Ryvon_EventLog::group.phtml';

    /**
     * @var string
     */
    const GROUP_BLOCK_CLASS = \Magento\Backend\Block\Template::class;

    /**
     * @var string
     */
    const HEADER_TEMPLATE = 'Ryvon_EventLog::heading/log.phtml';

    /**
     * @var string
     */
    const HEADER_BLOCK_CLASS = \Magento\Backend\Block\Template::class;

    /**
     * @var string
     */
    const ENTRY_TEMPLATE = 'Ryvon_EventLog::entry/log.phtml';

    /**
     * @var string
     */
    const ENTRY_BLOCK_CLASS = \Ryvon\EventLog\Block\Adminhtml\Digest\EntryBlock::class;

    /**
     * @var int
     */
    const SORT_ORDER = 50;

    /**
     * @var DigestSummarizer
     */
    private $summarizer;

    /**
     * @var DuplicateChecker
     */
    private $duplicateChecker;

    /**
     * @var bool
     */
    private $odd = false;

    /**
     * @var Entry[]
     */
    private $entries = [];

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @param Config $config
     * @param DigestSummarizer $summarizer
     * @param DuplicateCheckerFactory $duplicateCheckerFactory
     * @param LayoutInterface $layout
     */
    public function __construct(
        Config $config,
        DigestSummarizer $summarizer,
        DuplicateCheckerFactory $duplicateCheckerFactory,
        LayoutInterface $layout
    )
    {
        $this->summarizer = $summarizer;
        $this->layout = $layout;

        if ($config->getHideDuplicateEntries()) {
            $this->duplicateChecker = $duplicateCheckerFactory->create();
        }
    }

    /**
     * @return DigestSummarizer
     */
    protected function getSummarizer()
    {
        return $this->summarizer;
    }

    /**
     * @return LayoutInterface
     */
    protected function getLayout()
    {
        return $this->layout;
    }

    /**
     * @return string
     */
    public function getId()
    {
        if (static::GROUP_ID === false) {
            throw new \InvalidArgumentException('Subclass does not implement GROUP_ID');
        }
        return static::GROUP_ID;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return static::SORT_ORDER;
    }

    /**
     * @param Entry[] $entries
     * @return $this
     */
    public function setEntries($entries)
    {
        $this->entries = $entries;
        return $this;
    }

    /**
     * @return Entry[]
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @param bool $change
     * @return bool
     */
    protected function isOdd($change = true)
    {
        $current = $this->odd;
        if ($change) {
            $this->odd = !$this->odd;
        }
        return $current;
    }

    /**
     * @return string
     */
    public function render()
    {
        $entries = $this->getEntries();
        $hasUserContext = $this->hasUserContext($entries);

        $entitiesHtml = $this->renderEntries($entries, $hasUserContext);
        if (!$entitiesHtml) {
            return '';
        }

        $headingHtml = $this->renderHeading($entries, $hasUserContext);

        /** @var \Magento\Backend\Block\Template $block */
        $block = $this->getLayout()->createBlock(static::GROUP_BLOCK_CLASS);
        return $block->setTemplate(static::GROUP_TEMPLATE)
            ->addData([
                'title' => $this->getTitle(),
                'entries' => $entitiesHtml,
                'heading' => $headingHtml,
            ])
            ->toHtml();
    }

    /**
     * @param Entry[] $entries
     * @param bool $hasUserColumn
     * @return string
     */
    protected function renderEntries($entries, $hasUserColumn)
    {
        $entitiesHtml = [];

        $entriesToRender = [];

        // We loop through the reversed array so we show the latest duplicate (if duplicates are hidden)
        // This also makes sure the duplicate checker has access to the correct number of duplicates in the render below
        foreach (array_reverse($entries) as $entry) {
            if (!$this->duplicateChecker || !$this->duplicateChecker->isDuplicate($entry)) {
                // Since we're looping through a reversed array we need to build the render array reversed
                array_unshift($entriesToRender, $entry);
            }
        }

        foreach ($entriesToRender as $entry) {
            /** @var \Magento\Framework\View\Element\Template $block */
            $block = $this->getLayout()->createBlock(static::ENTRY_BLOCK_CLASS);

            $renderedEntry = $block
                ->setTemplate(static::ENTRY_TEMPLATE)
                ->addData([
                    'entry' => $entry,
                    'odd' => $this->isOdd(),
                    'user-column' => $hasUserColumn,
                    'duplicates' => $this->duplicateChecker->getCount($entry),
                ])
                ->toHtml();

            if ($renderedEntry) {
                $entitiesHtml[] = $renderedEntry;
            }
        }

        return implode('', $entitiesHtml);
    }

    /**
     * @param Entry[] $entries
     * @param bool $hasUserColumn
     * @return string
     */
    protected function renderHeading($entries, $hasUserColumn)
    {
        /** @var \Magento\Backend\Block\Template $block */
        $block = $this->getLayout()->createBlock(static::HEADER_BLOCK_CLASS);
        return $block->setTemplate(static::HEADER_TEMPLATE)
            ->addData([
                'title' => $this->getTitle(),
                'summary' => $this->getSummarizer()->buildSummaryMessage($entries),
                'user-column' => $hasUserColumn,
            ])
            ->toHtml();
    }

    /**
     * @param Entry[] $entries
     * @return bool
     */
    protected function hasUserContext($entries)
    {
        foreach ($entries as $entry) {
            $context = $entry->getEntryContext();
            if ($context->getData('user-name') || $context->getData('user-ip')) {
                return true;
            }
        }

        return false;
    }
}
