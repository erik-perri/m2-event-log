<?php

namespace Ryvon\EventLog\Helper\Group\Template;

use Ryvon\EventLog\Block\Adminhtml\Digest\EntryBlock;
use Ryvon\EventLog\Block\Adminhtml\TemplateBlock;

/**
 * TODO Load from XML?
 */
class DefaultTemplate implements TemplateInterface
{
    public function getGroupTemplateFile(): string
    {
        return 'Ryvon_EventLog::group.phtml';
    }

    public function getGroupBlockClass(): string
    {
        return TemplateBlock::class;
    }

    public function getHeaderTemplateFile(): string
    {
        return 'Ryvon_EventLog::heading/default.phtml';
    }

    public function getHeaderBlockClass(): string
    {
        return TemplateBlock::class;
    }

    public function getEntryTemplateFile(): string
    {
        return 'Ryvon_EventLog::entry/default.phtml';
    }

    public function getEntryBlockClass(): string
    {
        return EntryBlock::class;
    }
}
