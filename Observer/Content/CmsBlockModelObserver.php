<?php

namespace Ryvon\EventLog\Observer\Content;

use Ryvon\EventLog\Observer\AbstractModelObserver;
use Magento\Framework\Model\AbstractModel;

class CmsBlockModelObserver extends AbstractModelObserver
{
    /**
     * @param \Magento\Framework\Event $event
     * @return AbstractModel
     */
    public function getModel(\Magento\Framework\Event $event): AbstractModel
    {
        $entity = $event->getData('object');

        return $entity && $entity instanceof \Magento\Cms\Model\Block ? $entity : null;
    }

    /**
     * @param AbstractModel $entity
     * @param $action
     */
    protected function dispatch($entity, $action)
    {
        $this->getEventManager()->dispatch('event_log_info', [
            'group' => 'admin',
            'message' => 'Content block {cms-block} {action}.',
            'context' => [
                'store-view' => $this->getActiveStoreView(),
                'cms-block' => $entity->getData('title'),
                'cms-block-id' => $entity->getId(),
                'action' => $action,
            ],
        ]);
    }
}
