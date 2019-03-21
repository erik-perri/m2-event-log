<?php

namespace Ryvon\EventLog\Helper;

use Ryvon\EventLog\Helper\Group\GroupInterface;

class GroupSorter
{
    /**
     * @param GroupInterface[] $groups
     * @return array|GroupInterface[]
     */
    public function sort(array $groups): array
    {
        usort($groups, function (GroupInterface $groupA, GroupInterface $groupB) {
            $orderA = $groupA->getSortOrder();
            $orderB = $groupB->getSortOrder();
            if ($orderA !== $orderB) {
                return $orderA - $orderB;
            }
            return strcasecmp($groupA->getTitle(), $groupB->getTitle());
        });

        return $groups;
    }
}
