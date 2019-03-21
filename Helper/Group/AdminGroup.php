<?php

namespace Ryvon\EventLog\Helper\Group;

class AdminGroup extends AbstractGroup
{
    /**
     * @return string
     */
    public function getTitle(): string
    {
        return 'Admin Log';
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return 50;
    }
}
