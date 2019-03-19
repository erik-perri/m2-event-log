<?php

namespace Ryvon\EventLog\Observer\Content;

use Ryvon\EventLog\Observer\AbstractModelObserver;
use Magento\Framework\Model\AbstractModel;

class CmsPageModelObserver extends AbstractModelObserver
{
    /**
     * @param \Magento\Framework\Event $event
     * @return AbstractModel
     */
    public function getModel(\Magento\Framework\Event $event): AbstractModel
    {
        $entity = $event->getData('object');

        return $entity && $entity instanceof \Magento\Cms\Model\Page ? $entity : null;
    }

    /**
     * @param AbstractModel $entity
     * @param $action
     */
    protected function dispatch($entity, $action)
    {
        $this->getEventManager()->dispatch('event_log_info', [
            'group' => 'admin',
            'message' => 'Page {cms-page} {action}.',
            'context' => [
                'store-view' => $this->getActiveStoreView(),
                'cms-page' => $entity->getData('title'),
                'cms-page-id' => $entity->getId(),
                'action' => $action,
            ],
        ]);
    }
}
