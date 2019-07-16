<?php

namespace Ryvon\EventLog\Observer\Model;

use Magento\Customer\Model\Group;
use Magento\Framework\Event;
use Magento\Framework\Model\AbstractModel;

/**
 * Monitors the customer group model for changes.
 */
class CustomerGroupModelObserver extends AbstractModelObserver
{
    /**
     * @inheritDoc
     */
    public function findModel(Event $event): AbstractModel
    {
        $entity = $event->getData('object');

        return $entity && $entity instanceof Group ? $entity : null;
    }

    /**
     * @inheritDoc
     */
    protected function handle(AbstractModel $entity, string $action)
    {
        $this->getEventManager()->dispatch('event_log_info', [
            'group' => 'admin',
            'message' => 'Customer group {group} {action}.',
            'context' => [
                'group' => [
                    'handler' => 'group',
                    'text' => trim($entity->getData('code')),
                    'id' => (string)$entity->getId(),
                ],
                'action' => $action,
            ],
        ]);
    }
}
