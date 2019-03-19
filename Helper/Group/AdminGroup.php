<?php

namespace Ryvon\EventLog\Helper\Group;

class AdminGroup extends AbstractGroup
{
    /**
     * @var int
     */
    const SORT_ORDER = 60;

    /**
     * @return string
     */
    public function getId(): string
    {
        return 'admin';
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return 'Admin Log';
    }
}
