<?php

namespace Ryvon\EventLog\Helper\Group;

use Ryvon\EventLog\Model\Entry;

interface GroupInterface
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return int
     */
    public function getSortOrder();

    /**
     * @param Entry[] $entries
     * @return $this
     */
    public function setEntries($entries);

    /**
     * @return string
     */
    public function render();
}
