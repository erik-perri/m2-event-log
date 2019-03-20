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
    public function summarize($entries): array
    {
        $counts = [
            'error' => 0,
            'warning' => 0,
            'info' => 0,
            static::HIDDEN_DUPLICATES_KEY => 0,
        ];

        /** @var DuplicateChecker $duplicateChecker */
        $duplicateChecker = $this->duplicateCheckerFactory->create();

        $hideDuplicates = $this->config->getHideDuplicateEntries();

        foreach ($entries as $entry) {
            $level = $entry->getEntryLevel();

            if ($level === 'security') {
                $level = 'warning';
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
     * @param bool $includeEmpty
     * @return string
     */
    public function buildSummaryMessage($entries, $includeEmpty = false): string
    {
        $summary = $this->summarize($entries);
        return $this->getSummaryMessage($summary, $includeEmpty);
    }

    /**
     * @param array $summary
     * @param bool $includeEmpty
     * @return string
     */
    public function getSummaryMessage($summary, $includeEmpty): string
    {
        $map = [
            'error' => ['issue', 'issues'],
            'warning' => ['notice', 'notices'],
        ];
        $message = [];
        foreach ($summary as $key => $count) {
            if (!isset($map[$key])) {
                continue;
            }
            if (!$includeEmpty && !$count) {
                continue;
            }

            $message[] = number_format($count) . ' ' . ($count === 1 ? $map[$key][0] : $map[$key][1]);
        }
        return implode(', ', $message);
    }
}
