<?php

namespace Ryvon\EventLog\Observer\Catalog;

use Ryvon\EventLog\Observer\AbstractModificationObserver;
use Magento\Framework\Model\AbstractModel;
use Ryvon\EventLog\Helper\Group\AdminGroup;

class CategoryModificationObserver extends AbstractModificationObserver
{
    /**
     * @param \Magento\Framework\Event $event
     * @return AbstractModel
     */
    public function getEntity(\Magento\Framework\Event $event): AbstractModel
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
            'group' => AdminGroup::GROUP_ID,
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
