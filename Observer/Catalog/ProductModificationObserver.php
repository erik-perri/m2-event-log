<?php

namespace Ryvon\EventLog\Observer\Catalog;

use Ryvon\EventLog\Observer\AbstractModificationObserver;
use Magento\Framework\Model\AbstractModel;

class ProductModificationObserver extends AbstractModificationObserver
{
    /**
     * @param \Magento\Framework\Event $event
     * @return AbstractModel
     */
    public function getEntity(\Magento\Framework\Event $event): AbstractModel
    {
        $entity = $event->getData('product');

        return $entity && $entity instanceof \Magento\Catalog\Model\Product ? $entity : null;
    }

    /**
     * @param AbstractModel $entity
     * @param $action
     */
    protected function dispatch($entity, $action)
    {
        $this->getEventManager()->dispatch('event_log_info', [
            'group' => 'admin',
            'message' => 'Product {product} {action}.',
            'context' => [
                'store-view' => $this->getActiveStoreView(),
                'product' => $entity->getData('sku'),
                'action' => $action,
            ],
        ]);
    }
}
