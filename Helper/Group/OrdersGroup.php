<?php

namespace Ryvon\EventLog\Helper\Group;

use Ryvon\EventLog\Helper\Group\Template\OrdersTemplate;
use Ryvon\EventLog\Helper\Group\Template\TemplateInterface;

class OrdersGroup extends AbstractGroup
{
    /**
     * @return void
     */
    public function initialize()
    {
        $this->addHeadingLink('View All Orders', $this->getUrlBuilder()->getUrl('sales/order/index'));
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return 'Orders';
    }

    /**
     * @return TemplateInterface
     */
    public function getTemplate(): TemplateInterface
    {
        return new OrdersTemplate();
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return 30;
    }
}
