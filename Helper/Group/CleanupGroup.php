<?php

namespace Ryvon\EventLog\Helper\Group;

class CleanupGroup extends AbstractGroup
{
    /**
     * @var int
     */
    const SORT_ORDER = 70;

    /**
     * @return string
     */
    public function getId(): string
    {
        return 'cleanup';
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return 'Cleanup';
    }
}
