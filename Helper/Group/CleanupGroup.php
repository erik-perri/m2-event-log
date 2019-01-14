<?php

namespace Ryvon\EventLog\Helper\Group;

class CleanupGroup extends AbstractGroup
{
    /**
     * @var string
     */
    const GROUP_ID = 'cleanup';

    /**
     * @var int
     */
    const SORT_ORDER = 70;

    /**
     * @return string
     */
    public function getTitle()
    {
        return 'Cleanup';
    }
}
