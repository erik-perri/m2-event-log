<?php

namespace Ryvon\EventLog\Observer;

use Ryvon\EventLog\Helper\Group\AdminGroup;

class ProductModificationObserver extends AbstractModificationObserver
{
    /**
     * @param \Magento\Framework\Event $event
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function getEntity(\Magento\Framework\Event $event)
    {
        $entity = $event->getData('product');

        return $entity && $entity instanceof \Magento\Catalog\Model\Product ? $entity : null;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @param $action
     */
    protected function dispatch($entity, $action)
    {
        $this->getEventManager()->dispatch('event_log_info', [
            'group' => AdminGroup::GROUP_ID,
            'message' => 'Product {product} {action}.',
            'context' => [
                'store-view' => $this->getActiveStoreView(),
                'product' => $entity->getData('sku'),
                'action' => $action,
            ],
        ]);
    }
}
