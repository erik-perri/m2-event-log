<?php

namespace Ryvon\EventLog\Helper\Group;

use Ryvon\EventLog\Block\Adminhtml\Digest\OrderBlock;

class OrdersGroup extends AbstractLinksGroup
{
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
    public function getId(): string
    {
        return 'orders';
    }

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
