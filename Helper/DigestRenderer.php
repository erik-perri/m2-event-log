<?php

namespace Ryvon\EventLog\Helper;

use Ryvon\EventLog\Model\Entry;

class DigestRenderer
{
    /**
     * @var GroupSorter
     */
    private $groupSorter;

    /**
     * @var GroupFinder
     */
    private $groupFinder;

    /**
     * @param GroupSorter $groupSorter
     * @param GroupFinder $groupFinder
     */
    public function __construct(
        GroupSorter $groupSorter,
        GroupFinder $groupFinder
    )
    {
        $this->groupSorter = $groupSorter;
        $this->groupFinder = $groupFinder;
    }

    /**
     * @param Entry[] $entries
     * @return string
     */
    public function renderEntries($entries)
    {
        $groups = $this->groupSorter->groupEntries($entries);
        $return = [];

        foreach ($groups as $groupId => $groupEntries) {
            $group = $this->groupFinder->findGroup($groupId);

            // If the group doesn't exist it is likely handled by a plugin that is no longer installed,
            // we create a missing group handler to render it like a log.
            if (!$group) {
                $group = $this->groupFinder->addMissingGroup($groupId);
            }

            if ($group instanceof Group\AbstractLinksGroup) {
                $group->initialize();
            }

            $return[] = $group->setEntries($groupEntries)->render();
        }

        // Remove empty values
        $return = array_filter($return);

        return implode('', $return);
    }
}
