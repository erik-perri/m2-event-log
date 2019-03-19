<?php

namespace Ryvon\EventLog\Helper\Group;

use Ryvon\EventLog\Block\Adminhtml\Digest\OrderBlock;

class OrdersGroup extends AbstractLinksGroup
{
    /**
     * You should not use this in any plugins interacting with the event log.
     * They should use the string so they do not fail when the event log in not
     * installed.
     *
     * @var string
     */
    const GROUP_ID = 'orders';

    /**
     * @var string
     */
    const HEADER_TEMPLATE = 'Ryvon_EventLog::heading/orders.phtml';

    /**
     * @var string
     */
    const ENTRY_TEMPLATE = 'Ryvon_EventLog::entry/orders.phtml';

    /**
     * @var string
     */
    const ENTRY_BLOCK_CLASS = OrderBlock::class;

    /**
     * @var int
     */
    const SORT_ORDER = 30;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return 'Orders';
    }

    /**
     * @return void
     */
    public function initialize()
    {
        $this->addHeadingLink('View All Orders', $this->getUrlBuilder()->getUrl('sales/order/index'));
    }
}
