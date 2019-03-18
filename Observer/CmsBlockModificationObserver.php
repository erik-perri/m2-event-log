<?php

namespace Ryvon\EventLog\Observer;

use Ryvon\EventLog\Helper\Group\AdminGroup;

class CmsBlockModificationObserver extends AbstractModificationObserver
{
    /**
     * @param \Magento\Framework\Event $event
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function getEntity(\Magento\Framework\Event $event)
    {
        $entity = $event->getData('object');

        return $entity && $entity instanceof \Magento\Cms\Model\Block ? $entity : null;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @param $action
     */
    protected function dispatch($entity, $action)
    {
        $this->getEventManager()->dispatch('event_log_info', [
            'group' => AdminGroup::GROUP_ID,
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
