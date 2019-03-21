<?php

namespace Ryvon\EventLog\Helper\Group;

class CleanupGroup extends AbstractGroup
{
    /**
     * @return string
     */
    public function getTitle(): string
    {
        return 'Cleanup';
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return 70;
    }
}
