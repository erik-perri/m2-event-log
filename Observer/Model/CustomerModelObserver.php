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
    public function findModel(Event $event): ?AbstractModel
    {
        $entity = $event->getData('customer');

        return $entity && $entity instanceof Customer ? $entity : null;
    }

    /**
     * @inheritDoc
     */
    protected function handle(AbstractModel $entity, string $action)
    {
        // TODO This is catching creation as a modification
        $this->getEventManager()->dispatch('event_log_info', [
            'group' => 'admin',
            'message' => 'Customer {customer} {action}.',
            'context' => [
                'customer' => [
                    'text' => trim(sprintf('%s %s', $entity->getData('firstname'), $entity->getData('lastname'))),
                    'id' => $entity->getId(),
                ],
                'action' => $action,
                'store-view' => $this->getActiveStoreView(),
            ],
        ]);
    }
}
