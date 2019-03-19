<?php

namespace Ryvon\EventLog\Helper\Group;

class CleanupGroup extends AbstractGroup
{
    /**
     * You should not use this in any plugins interacting with the event log.
     * They should use the string so they do not fail when the event log in not
     * installed.
     *
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
    public function getTitle(): string
    {
        return 'Cleanup';
    }
}
