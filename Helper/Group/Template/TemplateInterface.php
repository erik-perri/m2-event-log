<?php

namespace Ryvon\EventLog\Helper\Group\Template;

/**
 * Group template configuration.
 */
interface TemplateInterface
{
    /**
     * Retrieves the file used for the group template.
     *
     * @return string
     */
    public function getGroupTemplateFile(): string;

    /**
     * Retrieves the class used for the group block template.
     *
     * @return string
     */
    public function getGroupBlockClass(): string;

    /**
     * Retrieves the file used for the header template.
     *
     * @return string
     */
    public function getHeaderTemplateFile(): string;

    /**
     * Retrieves the class used for the header block template.
     *
     * @return string
     */
    public function getHeaderBlockClass(): string;

    /**
     * Retrieves the file used for the entry template.
     *
     * @return string
     */
    public function getEntryTemplateFile(): string;

    /**
     * Retrieves the class used for the entry block template.
     *
     * @return string
     */
    public function getEntryBlockClass(): string;
}
