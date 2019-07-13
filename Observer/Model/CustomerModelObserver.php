<?php

namespace Ryvon\EventLog\Observer\Model;

use Magento\Customer\Model\Customer;
use Magento\Framework\Event;
use Magento\Framework\Model\AbstractModel;

/**
 * Monitors the customer model for changes.
 */
class CustomerModelObserver extends AbstractModelObserver
{
    /**
     * @inheritDoc
     */
    public function findModel(Event $event): AbstractModel
    {
        $entity = $event->getData('customer');

        return $entity && $entity instanceof Customer ? $entity : null;
    }

    /**
     * @inheritDoc
     */
    protected function handle(AbstractModel $entity, string $action)
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
