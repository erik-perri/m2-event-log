<?php

namespace Ryvon\EventLog\Helper;

use Ryvon\EventLog\Model\Entry;

class DuplicateChecker
{
    /**
     * @var int[]
     */
    private $shown = [];

    /**
     * @param Entry $entry
     * @return bool
     */
    public function isExcluded(Entry $entry): bool
    {
        return $entry->getEntryLevel() === DigestHelper::LEVEL_SECURITY;
    }

    /**
     * @param Entry $entry
     * @param bool $addToList
     * @return bool
     */
    public function isDuplicate(Entry $entry, $addToList = true): bool
    {
        if ($this->isExcluded($entry)) {
            return false;
        }

        $hash = $this->getHash($entry);

        if (isset($this->shown[$hash])) {
            $this->shown[$hash]++;
            return true;
        }

        if ($addToList) {
            $this->shown[$hash] = 1;
        }

        return false;
    }

    /**
     * @param Entry $entry
     * @return int
     */
    public function getCount(Entry $entry): int
    {
        return $this->shown[$this->getHash($entry)] ?? 0;
    }

    /**
     * @param Entry $entry
     * @return string
     */
    protected function getHash(Entry $entry): string
    {
        return md5(
            $entry->getEntryGroup() .
            $entry->getEntryLevel() .
            $entry->getEntryMessage() .
            $entry->getEntryContext()->convertToJson()
        );
    }

    /**
     * @return void
     */
    public function reset()
    {
        $this->shown = [];
    }
}
