<?php

namespace Ryvon\EventLog\Helper\Group;

use Ryvon\EventLog\Helper\DigestSummarizer;
use Ryvon\EventLog\Helper\DuplicateChecker;
use Ryvon\EventLog\Helper\DuplicateCheckerFactory;
use Ryvon\EventLog\Model\Config;
use Ryvon\EventLog\Model\Entry;
use Magento\Backend\Block\Template;
use Magento\Framework\App\Area;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;

abstract class AbstractGroup implements GroupInterface
{
    /**
     * @var string
     */
    const GROUP_TEMPLATE = 'Ryvon_EventLog::group.phtml';

    /**
     * @var string
     */
    const GROUP_BLOCK_CLASS = Template::class;

    /**
     * @var string
     */
    const HEADER_TEMPLATE = 'Ryvon_EventLog::heading/log.phtml';

    /**
     * @var string
     */
    const HEADER_BLOCK_CLASS = Template::class;

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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Config $config
     * @param DigestSummarizer $summarizer
     * @param DuplicateCheckerFactory $duplicateCheckerFactory
     * @param LayoutInterface $layout
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        DigestSummarizer $summarizer,
        DuplicateCheckerFactory $duplicateCheckerFactory,
        LayoutInterface $layout,
        StoreManagerInterface $storeManager
    )
    {
        $this->summarizer = $summarizer;
        $this->layout = $layout;

        if ($config->getHideDuplicateEntries()) {
            $this->duplicateChecker = $duplicateCheckerFactory->create();
        }

        $this->storeManager = $storeManager;
    }

    /**
     * @return DigestSummarizer
     */
    protected function getSummarizer(): DigestSummarizer
    {
        return $this->summarizer;
    }

    /**
     * @return LayoutInterface
     */
    protected function getLayout(): LayoutInterface
    {
        return $this->layout;
    }

    /**
     * @return StoreManagerInterface
     */
    protected function getStoreManager(): StoreManagerInterface
    {
        return $this->storeManager;
    }

    /**
     * @return string
     */
    abstract public function getId(): string;

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return static::SORT_ORDER;
    }

    /**
     * @param Entry[] $entries
     * @return $this
     */
    public function setEntries($entries): GroupInterface
    {
        $this->entries = $entries;
        return $this;
    }

    /**
     * @return Entry[]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * @param bool $change
     * @return bool
     */
    protected function isOdd($change = true): bool
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
    public function render(): string
    {
        $entries = $this->getEntries();
        $hasUserContext = $this->hasUserContext($entries);

        $entitiesHtml = $this->renderEntries($entries, $hasUserContext);
        if (!$entitiesHtml) {
            return '';
        }

        $headingHtml = $this->renderHeading($entries, $hasUserContext);

        return $this->createBlock(static::GROUP_BLOCK_CLASS)
            ->setTemplate(static::GROUP_TEMPLATE)
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
    protected function renderEntries($entries, $hasUserColumn): string
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

        foreach ($entriesToRender as $index => $entry) {
            $renderedEntry = $this->createBlock(static::ENTRY_BLOCK_CLASS)
                ->setTemplate(static::ENTRY_TEMPLATE)
                ->addData([
                    'entry' => $entry,
                    'odd' => $this->isOdd(),
                    'user-column' => $hasUserColumn,
                    'single-store-mode' => $this->storeManager->isSingleStoreMode(),
                    'duplicates' => $this->duplicateChecker ? $this->duplicateChecker->getCount($entry) : 0,
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
    protected function renderHeading($entries, $hasUserColumn): string
    {
        return $this->createBlock(static::HEADER_BLOCK_CLASS)
            ->setTemplate(static::HEADER_TEMPLATE)
            ->addData([
                'title' => $this->getTitle(),
                'summary' => $this->getSummarizer()->buildSummaryMessage($entries),
                'user-column' => $hasUserColumn,
                'single-store-mode' => $this->storeManager->isSingleStoreMode(),
            ])
            ->toHtml();
    }

    /**
     * @param string $type
     * @param string $name
     * @param array $arguments
     * @return Template
     */
    protected function createBlock($type, $name = '', $arguments = []): Template
    {
        /** @var Template $block */
        $block = $this->getLayout()->createBlock($type, $name, $arguments);
        // We need to set the area on the block or Magento will set it to crontab
        // and fail to find the templates when running this code through the cron.
        $block->setData('area', Area::AREA_ADMINHTML);
        return $block;
    }

    /**
     * @param Entry[] $entries
     * @return bool
     */
    protected function hasUserContext($entries): bool
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
