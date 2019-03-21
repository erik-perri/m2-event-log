<?php

namespace Ryvon\EventLog\Helper\Group\Template;

use Ryvon\EventLog\Block\Adminhtml\Digest\EntryBlock;
use Ryvon\EventLog\Block\Adminhtml\TemplateBlock;

/**
 * Default group template configuration.
 *
 * TODO Load from XML?
 */
class DefaultTemplate implements TemplateInterface
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getGroupTemplateFile(): string
    {
        return 'Ryvon_EventLog::group.phtml';
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getGroupBlockClass(): string
    {
        return TemplateBlock::class;
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getHeaderTemplateFile(): string
    {
        return 'Ryvon_EventLog::heading/default.phtml';
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getHeaderBlockClass(): string
    {
        return TemplateBlock::class;
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getEntryTemplateFile(): string
    {
        return 'Ryvon_EventLog::entry/default.phtml';
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getEntryBlockClass(): string
    {
        return EntryBlock::class;
    }
}
