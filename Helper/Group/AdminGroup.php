<?php

namespace Ryvon\EventLog\Helper\Group;

/**
 * Log group for admin actions, code 'admin'.
 */
class AdminGroup extends AbstractGroup
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Admin Log';
    }

    /**
     * @inheritdoc
     *
     * @return int
     */
    public function getSortOrder(): int
    {
        return 50;
    }
}
