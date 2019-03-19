<?php

namespace Ryvon\EventLog\Observer\Customers;

use Ryvon\EventLog\Observer\AbstractModelObserver;
use Magento\Framework\Model\AbstractModel;

class CustomerModelObserver extends AbstractModelObserver
{
    /**
     * @param \Magento\Framework\Event $event
     * @return AbstractModel
     */
    public function getModel(\Magento\Framework\Event $event): AbstractModel
    {
        $entity = $event->getData('customer');

        return $entity && $entity instanceof \Magento\Customer\Model\Customer ? $entity : null;
    }

    /**
     * @param \Magento\Customer\Model\Customer $entity
     * @param $action
     */
    protected function dispatch($entity, $action)
    {
        $this->getEventManager()->dispatch('event_log_info', [
            'group' => 'admin',
            'message' => 'Customer {customer} {action}.',
            'context' => [
                'store-view' => $this->getActiveStoreView(),
                'customer' => trim(sprintf('%s %s', $entity->getData('firstname'), $entity->getData('lastname'))),
                'customer-id' => (string)$entity->getId(),
                'action' => $action,
            ],
        ]);
    }
}
