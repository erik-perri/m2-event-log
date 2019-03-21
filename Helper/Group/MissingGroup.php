<?php

namespace Ryvon\EventLog\Helper\Group;

/**
 * Log group for any defined groups that don't have a group class associated with it. Ideally this will only be used on
 * log entries that had the plugin which created them removed. The title is generated based on the group id by replacing
 * dashes and underscores with spaces.
 */
class MissingGroup extends AbstractGroup
{
    /**
     * @var string
     */
    private $title;

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Set the title of the missing group.
     *
     * @param string $title
     * @return MissingGroup
     */
    public function setTitle($title): MissingGroup
    {
        $this->title = $title;
        return $this;
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
