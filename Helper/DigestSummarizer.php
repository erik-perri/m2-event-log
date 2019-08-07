<?php

namespace Ryvon\EventLog\Helper;

use Ryvon\EventLog\Model\Entry;

class DigestSummarizer
{
    /**
     * The key used in the summary array for hidden duplicates.
     */
    const HIDDEN_DUPLICATES_KEY = 'hidden-duplicates';

    /**
     * @var DuplicateCheckerFactory
     */
    private $duplicateCheckerFactory;

    /**
     * @var array
     */
    private $typeMap = [
        'error' => ['issue', 'issues'],
        'warning' => ['notice', 'notices'],
    ];

    /**
     * @param DuplicateCheckerFactory $duplicateCheckerFactory
     */
    public function __construct(DuplicateCheckerFactory $duplicateCheckerFactory)
    {
        $this->duplicateCheckerFactory = $duplicateCheckerFactory;
    }

    /**
     * @param Entry[] $entries
     * @param bool $hideDuplicates
     * @return array
     */
    public function summarize($entries, bool $hideDuplicates): array
    {
        $counts = [
            'error' => 0,
            'warning' => 0,
            'info' => 0,
            static::HIDDEN_DUPLICATES_KEY => 0,
        ];

        if (!$entries) {
            return $counts;
        }

        /** @var DuplicateChecker $duplicateChecker */
        $duplicateChecker = $this->duplicateCheckerFactory->create();

        foreach ($entries as $entry) {
            $level = $entry->getEntryLevel();

            if ($hideDuplicates && $duplicateChecker->isDuplicate($entry)) {
                $counts[static::HIDDEN_DUPLICATES_KEY] = isset($counts[static::HIDDEN_DUPLICATES_KEY]) ? $counts[static::HIDDEN_DUPLICATES_KEY] + 1 : 1;
                continue;
            }

            if ($level === 'security') {
                $level = 'warning';
            }

            $counts[$level] = isset($counts[$level]) ? $counts[$level] + 1 : 1;
        }

        return $counts;
    }

    /**
     * @param array $summary
     * @param bool $includeEmpty
     * @return string
     */
    public function getSummaryMessage($summary, $includeEmpty): string
    {
        $map = $this->getTypeMap();
        $message = [];
        foreach ($summary as $key => $count) {
            if (!isset($map[$key])) {
                continue;
            }
            if (!$includeEmpty && !$count) {
                continue;
            }

            $text = $key;
            if (is_string($map[$key])) {
                $text = $map[$key];
            } else if (isset($map[$key][0], $map[$key][1])) {
                $text = ($count === 1 ? $map[$key][0] : $map[$key][1]);
            }

            $message[] = number_format($count) . ' ' . __($text);
        }
        return implode(', ', $message);
    }

    /**
     * @return array
     */
    public function getTypeMap(): array
    {
        return $this->typeMap;
    }

    /**
     * @param array $typeMap
     * @return DigestSummarizer
     */
    public function setTypeMap(array $typeMap): DigestSummarizer
    {
        $this->typeMap = $typeMap;
        return $this;
    }
}
