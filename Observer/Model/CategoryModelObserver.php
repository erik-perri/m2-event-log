<?php

namespace Ryvon\EventLog\Observer\Model;

use Magento\Catalog\Model\Category;
use Magento\Framework\Event;
use Magento\Framework\Model\AbstractModel;

/**
 * Monitors the category model for changes.
 */
class CategoryModelObserver extends AbstractModelObserver
{
    /**
     * @inheritDoc
     */
    public function findModel(Event $event): ?AbstractModel
    {
        $entity = $event->getData('category');

        return $entity && $entity instanceof Category ? $entity : null;
    }

    /**
     * @inheritDoc
     */
    protected function handle(AbstractModel $entity, string $action)
    {
        $this->getEventManager()->dispatch('event_log_info', [
            'group' => 'admin',
            'message' => 'Category {category} {action}.',
            'context' => [
                'category' => [
                    'text' => $entity->getData('name'),
                    'id' => $entity->getId(),
                ],
                'action' => $action,
                'store-view' => $this->getActiveStoreView(),
            ],
        ]);
    }
}
