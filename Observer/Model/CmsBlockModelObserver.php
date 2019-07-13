<?php

namespace Ryvon\EventLog\Observer\Model;

use Magento\Cms\Model\Block;
use Magento\Framework\Event;
use Magento\Framework\Model\AbstractModel;

/**
 * Monitors the CMS block model for changes.
 */
class CmsBlockModelObserver extends AbstractModelObserver
{
    /**
     * @inheritDoc
     */
    public function findModel(Event $event): AbstractModel
    {
        $entity = $event->getData('object');

        return $entity && $entity instanceof Block ? $entity : null;
    }

    /**
     * @inheritDoc
     */
    protected function handle(AbstractModel $entity, string $action)
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
