<?php

namespace Ryvon\EventLog\Helper;

use Ryvon\EventLog\Model\Entry;

class GroupSorter
{
    const UNKNOWN_GROUP_ORDER = Group\AbstractGroup::SORT_ORDER + 5;

    /**
     * @var GroupFinder
     */
    private $groupFinder;

    /**
     * @param GroupFinder $groupFinder
     */
    public function __construct(GroupFinder $groupFinder)
    {
        $this->groupFinder = $groupFinder;
    }

    /**
     * @param Entry[] $entries
     * @return Entry[][]
     */
    public function groupEntries($entries)
    {
        $groups = [];

        foreach ($entries as $entry) {
            $groups[$entry->getEntryGroup()][] = $entry;
        }

        uksort($groups, function ($groupA, $groupB) {
            $groupInstanceA = $this->groupFinder->findGroup($groupA);
            $groupInstanceB = $this->groupFinder->findGroup($groupB);
            $orderA = $groupInstanceA ? $groupInstanceA->getSortOrder() : static::UNKNOWN_GROUP_ORDER;
            $orderB = $groupInstanceB ? $groupInstanceB->getSortOrder() : static::UNKNOWN_GROUP_ORDER;
            if ($orderA !== $orderB) {
                return $orderA - $orderB;
            }
            return strcasecmp($groupA, $groupB);
        });

        return $groups;
    }
}
