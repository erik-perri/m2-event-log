<?php

namespace Ryvon\EventLog\Observer\Catalog;

use Ryvon\EventLog\Observer\AbstractModelObserver;
use Magento\Framework\Model\AbstractModel;

class CategoryModelObserver extends AbstractModelObserver
{
    /**
     * @param \Magento\Framework\Event $event
     * @return AbstractModel
     */
    public function getModel(\Magento\Framework\Event $event): AbstractModel
    {
        $entity = $event->getData('category');

        return $entity && $entity instanceof \Magento\Catalog\Model\Category ? $entity : null;
    }

    /**
     * @param AbstractModel $entity
     * @param $action
     */
    protected function dispatch($entity, $action)
    {
        $this->getEventManager()->dispatch('event_log_info', [
            'group' => 'admin',
            'message' => 'Category {category} {action}.',
            'context' => [
                'store-view' => $this->getActiveStoreView(),
                'category' => $entity->getData('name'),
                'category-id' => $entity->getId(),
                'action' => $action,
            ],
        ]);
    }
}
