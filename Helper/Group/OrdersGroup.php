<?php

namespace Ryvon\EventLog\Helper\Group;

use Ryvon\EventLog\Helper\Group\Template\OrdersTemplate;
use Ryvon\EventLog\Helper\Group\Template\TemplateInterface;

/**
 * Log group for orders made during the digest time window.
 */
class OrdersGroup extends AbstractGroup
{
    /**
     * @inheritdoc
     *
     * @return void
     */
    public function initialize()
    {
        $this->addHeadingLink('View All Orders', $this->getUrlBuilder()->getUrl('sales/order/index'));
    }

    /**
     * @inheritdoc
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Orders';
    }

    /**
     * @inheritdoc
     *
     * @return TemplateInterface
     */
    public function getTemplate(): TemplateInterface
    {
        return new OrdersTemplate();
    }

    /**
     * @inheritdoc
     *
     * @return int
     */
    public function getSortOrder(): int
    {
        return 30;
    }
}
