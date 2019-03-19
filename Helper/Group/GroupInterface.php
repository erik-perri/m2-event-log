<?php

namespace Ryvon\EventLog\Helper\Group;

use Ryvon\EventLog\Model\Entry;

interface GroupInterface
{
    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return int
     */
    public function getSortOrder(): int;

    /**
     * @param Entry[] $entries
     * @return $this
     */
    public function setEntries($entries): GroupInterface;

    /**
     * @return string
     */
    public function render(): string;
}
