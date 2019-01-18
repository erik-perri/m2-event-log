<?php

namespace Ryvon\EventLog\Observer;

use Ryvon\EventLog\Helper\Group\AdminGroup;

class CustomerModificationObserver extends AbstractModificationObserver
{
    /**
     * @param \Magento\Framework\Event $event
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function getEntity(\Magento\Framework\Event $event)
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
            'group' => AdminGroup::GROUP_ID,
            'message' => 'Customer {customer} {action}.',
            'context' => [
                'customer' => trim(sprintf('%s %s', $entity->getData('firstname'), $entity->getData('lastname'))),
                'customer-id' => (string)$entity->getId(),
                'action' => $action,
            ],
        ]);
    }
}
