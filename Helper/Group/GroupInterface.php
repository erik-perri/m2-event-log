<?php

namespace Ryvon\EventLog\Helper\Group;

use Ryvon\EventLog\Helper\Group\Template\TemplateInterface;
use Ryvon\EventLog\Model\EntryCollection;

/**
 * Interface for log groups that need customization.
 */
interface GroupInterface
{
    /**
     * Retrieves the title of the group.
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Retrieves the sort order of the group. Any number is supported, 40 is the lowest default and 60 is the highest.
     *
     * @return int
     */
    public function getSortOrder(): int;

    /**
     * Set the collection of entries that belong to the group.
     *
     * @param EntryCollection $entries
     * @return GroupInterface
     */
    public function setEntryCollection(EntryCollection $entries): GroupInterface;

    /**
     * Retrieves the collection of entries that belong to the group.
     *
     * @return EntryCollection
     */
    public function getEntryCollection(): EntryCollection;

    /**
     * Retrieves the template settings for this group.
     *
     * @return TemplateInterface
     */
    public function getTemplate(): TemplateInterface;

    /**
     * Retrieves the links the group should render in the header.
     *
     * @return array
     */
    public function getHeadingLinks(): array;
}
