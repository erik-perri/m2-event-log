<?php

namespace Ryvon\EventLog\Helper\Group;

use Ryvon\EventLog\Helper\Group\Template\TemplateInterface;
use Ryvon\EventLog\Model\EntryCollection;

interface GroupInterface
{
    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return int
     */
    public function getSortOrder(): int;

    /**
     * @param EntryCollection $entries
     * @return GroupInterface
     */
    public function setEntryCollection(EntryCollection $entries): GroupInterface;

    /**
     * @return EntryCollection
     */
    public function getEntryCollection(): EntryCollection;

    /**
     * @return TemplateInterface
     */
    public function getTemplate(): TemplateInterface;

    /**
     * @return array
     */
    public function getHeadingLinks(): array;
}
