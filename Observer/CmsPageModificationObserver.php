<?php

namespace Ryvon\EventLog\Observer;

use Ryvon\EventLog\Helper\Group\AdminGroup;

class CmsPageModificationObserver extends AbstractModificationObserver
{
    /**
     * @param \Magento\Framework\Event $event
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function getEntity(\Magento\Framework\Event $event)
    {
        $entity = $event->getData('object');

        return $entity && $entity instanceof \Magento\Cms\Model\Page ? $entity : null;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @param $action
     */
    protected function dispatch($entity, $action)
    {
        $this->getEventManager()->dispatch('event_log_info', [
            'group' => AdminGroup::GROUP_ID,
            'message' => 'Page {cms-page} {action}.',
            'context' => [
                'cms-page' => $entity->getData('title'),
                'cms-page-id' => $entity->getId(),
                'action' => $action,
            ],
        ]);
    }
}
