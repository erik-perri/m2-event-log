<?php

namespace Ryvon\EventLog\Helper\Group;

class AdminGroup extends AbstractGroup
{
    /**
     * @var string
     */
    const GROUP_ID = 'admin';

    /**
     * @var int
     */
    const SORT_ORDER = 60;

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Admin Log';
    }
}
