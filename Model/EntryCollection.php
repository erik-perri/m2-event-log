<?php

namespace Ryvon\EventLog\Model;

use Ryvon\EventLog\Helper\DigestSummarizer;
use Ryvon\EventLog\Helper\DuplicateChecker;
use Ryvon\EventLog\Helper\DuplicateCheckerFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * @property Entry[] $_items
 * @method Entry getFirstItem()
 * @method Entry getLastItem()
 */
class EntryCollection extends AbstractCollection
{
    /**
     * @var EntryResourceModel
     */
    private $entryResourceModel;

    /**
     * @var DuplicateChecker
     */
    private $duplicateChecker;

    /**
     * @var bool
     */
    private $hideDuplicates = false;

    /**
     * @var DigestSummarizer
     */
    private $digestSummarizer;

    /**
     * EntryCollection constructor.
     * @param EntryResourceModel $entryResourceModel
     * @param DuplicateCheckerFactory $duplicateCheckerFactory
     * @param DigestSummarizer $digestSummarizer
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        EntryResourceModel $entryResourceModel,
        DuplicateCheckerFactory $duplicateCheckerFactory,
        DigestSummarizer $digestSummarizer,
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);

        $this->entryResourceModel = $entryResourceModel;
        $this->duplicateChecker = $duplicateCheckerFactory->create();
        $this->digestSummarizer = $digestSummarizer;
    }

    /**
     * @param bool $hideDuplicates
     * @return EntryCollection
     */
    public function setHideDuplicates(bool $hideDuplicates): EntryCollection
    {
        $this->hideDuplicates = $hideDuplicates;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasUserContext(): bool
    {
        foreach ($this->getItems() as $item) {
            /** @var Entry $item */
            $context = $item->getEntryContext();
            if ($context->getData('.user')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve collection items.
     *
     * @return Entry[]
     */
    public function getItems(): array
    {
        $this->load();

        if ($this->shouldHideDuplicates()) {
            $this->duplicateChecker->reset();

            $filteredItems = [];

            // We loop through the reversed array so we show the latest duplicate (if duplicates are hidden)
            foreach (array_reverse($this->_items) as $item) {
                /** @var Entry $item */
                if (!$this->duplicateChecker->isDuplicate($item)) {
                    // Since we're looping through a reversed array we need to build the render array reversed
                    array_unshift($filteredItems, $item);
                }
            }

            return $filteredItems;
        }

        return $this->_items;
    }

    /**
     * @return bool
     */
    public function shouldHideDuplicates(): bool
    {
        return $this->hideDuplicates;
    }

    /**
     * @param $entry
     * @return int
     */
    public function getDuplicateCount($entry): int
    {
        if ($this->shouldHideDuplicates()) {
            return $this->duplicateChecker->getCount($entry);
        }

        return 0;
    }

    /**
     * @param bool $includeEmpty
     * @return string
     */
    public function buildSummaryMessage($includeEmpty = false): string
    {
        $summary = $this->digestSummarizer->summarize(
            $this->getUnfilteredItems(),
            $this->shouldHideDuplicates()
        );
        return $this->digestSummarizer->getSummaryMessage($summary, $includeEmpty);
    }

    /**
     * Retrieve unfiltered (of duplicates) collection items.
     *
     * @return Entry[]
     */
    public function getUnfilteredItems(): array
    {
        return $this->load()->_items;
    }

    /**
     * @noinspection MagicMethodsValidityInspection
     * @return void
     */
    protected function _construct()
    {
        $this->_setIdFieldName('entry_id');
        $this->_init(Entry::class, EntryResourceModel::class);
    }

    /**
     * @return AbstractCollection
     */
    protected function _afterLoad(): AbstractCollection
    {
        parent::_afterLoad();

        foreach ($this->_items as $item) {
            /** @var Entry $item */
            $this->entryResourceModel->loadEntryContext($item);
        }

        return $this;
    }
}
