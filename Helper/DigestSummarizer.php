<?php

namespace Ryvon\EventLog\Helper;

use Ryvon\EventLog\Model\Config;
use Ryvon\EventLog\Model\Entry;

class DigestSummarizer
{
    const HIDDEN_DUPLICATES_KEY = 'hidden-duplicates';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var DuplicateCheckerFactory
     */
    private $duplicateCheckerFactory;

    /**
     * @param Config $config
     * @param DuplicateCheckerFactory $duplicateCheckerFactory
     */
    public function __construct(
        Config $config,
        DuplicateCheckerFactory $duplicateCheckerFactory
    )
    {
        $this->config = $config;
        $this->duplicateCheckerFactory = $duplicateCheckerFactory;
    }

    /**
     * @param Entry[] $entries
     * @return array
     */
    public function summarize($entries)
    {
        $counts = [
            DigestHelper::LEVEL_ERROR => 0,
            DigestHelper::LEVEL_WARNING => 0,
            DigestHelper::LEVEL_INFO => 0,
            static::HIDDEN_DUPLICATES_KEY => 0,
        ];

        $duplicateChecker = $this->duplicateCheckerFactory->create();

        $hideDuplicates = $this->config->getHideDuplicateEntries();

        foreach ($entries as $entry) {
            $level = $entry->getEntryLevel();

            if ($level === DigestHelper::LEVEL_SECURITY) {
                $level = DigestHelper::LEVEL_WARNING;
            }

            if ($hideDuplicates && $duplicateChecker->isDuplicate($entry)) {
                $counts[static::HIDDEN_DUPLICATES_KEY] = isset($counts[static::HIDDEN_DUPLICATES_KEY]) ? $counts[static::HIDDEN_DUPLICATES_KEY] + 1 : 1;
                continue;
            }

            $counts[$level] = isset($counts[$level]) ? $counts[$level] + 1 : 1;
        }

        return $counts;
    }

    /**
     * @param Entry[] $entries
     * @return string
     */
    public function buildSummaryMessage($entries)
    {
        $summary = $this->summarize($entries);
        return $this->getSummaryMessage($summary);
    }

    /**
     * @param array $summary
     * @return string
     */
    public function getSummaryMessage($summary)
    {
        $map = [
            DigestHelper::LEVEL_WARNING => ['alert', 'alerts'],
            DigestHelper::LEVEL_ERROR => ['issue', 'issues'],
        ];
        $message = [];
        foreach ($summary as $key => $count) {
            if ($count && isset($map[$key])) {
                $message[] = number_format($count) . ' ' . ($count === 1 ? $map[$key][0] : $map[$key][1]);
            }
        }
        return implode(', ', $message);
    }
}
