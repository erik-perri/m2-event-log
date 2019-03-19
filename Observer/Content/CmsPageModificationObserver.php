<?php

namespace Ryvon\EventLog\Observer\Content;

use Ryvon\EventLog\Observer\AbstractModificationObserver;
use Magento\Framework\Model\AbstractModel;
use Ryvon\EventLog\Helper\Group\AdminGroup;

class CmsPageModificationObserver extends AbstractModificationObserver
{
    /**
     * @param \Magento\Framework\Event $event
     * @return AbstractModel
     */
    public function getEntity(\Magento\Framework\Event $event): AbstractModel
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
            'group' => AdminGroup::GROUP_ID,
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
