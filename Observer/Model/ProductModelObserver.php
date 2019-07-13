<?php

namespace Ryvon\EventLog\Observer\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event;
use Magento\Framework\Model\AbstractModel;

/**
 * Monitors the product model for changes.
 */
class ProductModelObserver extends AbstractModelObserver
{
    /**
     * @inheritDoc
     */
    public function findModel(Event $event): AbstractModel
    {
        $entity = $event->getData('product');

        return $entity && $entity instanceof Product ? $entity : null;
    }

    /**
     * @inheritDoc
     */
    protected function handle(AbstractModel $entity, string $action)
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
