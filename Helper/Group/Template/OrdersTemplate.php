<?php

namespace Ryvon\EventLog\Helper\Group\Template;

use Ryvon\EventLog\Block\Adminhtml\Digest\OrderBlock;

class OrdersTemplate extends DefaultTemplate
{
    public function getHeaderTemplateFile(): string
    {
        return 'Ryvon_EventLog::heading/orders.phtml';
    }

    public function getEntryTemplateFile(): string
    {
        return 'Ryvon_EventLog::entry/orders.phtml';
    }

    public function getEntryBlockClass(): string
    {
        return OrderBlock::class;
    }
}
