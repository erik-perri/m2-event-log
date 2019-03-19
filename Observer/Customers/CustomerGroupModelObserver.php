<?php

namespace Ryvon\EventLog\Observer\Customers;

use Ryvon\EventLog\Observer\AbstractModelObserver;
use Magento\Framework\Model\AbstractModel;

class CustomerGroupModelObserver extends AbstractModelObserver
{
    /**
     * @param \Magento\Framework\Event $event
     * @return AbstractModel
     */
    public function getModel(\Magento\Framework\Event $event): AbstractModel
    {
        $entity = $event->getData('object');

        return $entity && $entity instanceof \Magento\Customer\Model\Group ? $entity : null;
    }

    /**
     * @param \Magento\Customer\Model\Group $entity
     * @param $action
     */
    protected function dispatch($entity, $action)
    {
        $this->getEventManager()->dispatch('event_log_info', [
            'group' => 'admin',
            'message' => 'Customer group {customer-group} {action}.',
            'context' => [
                'customer-group' => trim($entity->getCode()),
                'customer-id' => (string)$entity->getId(),
                'action' => $action,
            ],
        ]);
    }
}
