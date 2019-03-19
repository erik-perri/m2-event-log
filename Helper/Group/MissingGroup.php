<?php

namespace Ryvon\EventLog\Helper\Group;

class MissingGroup extends AbstractGroup
{
    /**
     * @var int
     */
    const SORT_ORDER = 55;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return MissingGroup
     */
    public function setId($id): MissingGroup
    {
        $this->id = $id;
        return $this;
    }

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
}
