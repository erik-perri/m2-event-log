<?php

namespace Ryvon\EventLog\Observer\Model;

use Magento\Cms\Model\Page;
use Magento\Framework\Event;
use Magento\Framework\Model\AbstractModel;

/**
 * Monitors the CMS page model for changes.
 */
class CmsPageModelObserver extends AbstractModelObserver
{
    /**
     * @inheritDoc
     */
    public function findModel(Event $event): AbstractModel
    {
        $entity = $event->getData('object');

        return $entity && $entity instanceof Page ? $entity : null;
    }

    /**
     * @inheritDoc
     */
    protected function handle(AbstractModel $entity, string $action)
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
