<?php

namespace Ryvon\EventLog\Helper\Group;

class MissingGroup extends AbstractGroup
{
    /**
     * @var string
     */
    private $title;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return MissingGroup
     */
    public function setTitle($title): MissingGroup
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return 60;
    }
}
