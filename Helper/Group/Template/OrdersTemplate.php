<?php

namespace Ryvon\EventLog\Helper\Group\Template;

use Ryvon\EventLog\Block\Adminhtml\Digest\OrderBlock;

/**
 * Orders group template configuration.
 */
class OrdersTemplate extends DefaultTemplate
{
    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getHeaderTemplateFile(): string
    {
        return 'Ryvon_EventLog::heading/orders.phtml';
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getEntryTemplateFile(): string
    {
        return 'Ryvon_EventLog::entry/orders.phtml';
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getEntryBlockClass(): string
    {
        return OrderBlock::class;
    }
}
