<?php

namespace Ryvon\EventLog\Observer\Event;

use Magento\Framework\Event;

/**
 * Handles the flush cache files events.
 */
class CacheFlushFilesEventObserver extends AbstractEventObserver
{
    /**
     * @inheritDoc
     */
    protected function handle(Event $event)
    {
        $cache = 'unknown files cache';
        if (stripos($event->getName(), 'catalog_images') !== false) {
            $cache = 'catalog images cache';
        } elseif (stripos($event->getName(), 'static_files') !== false) {
            $cache = 'static files cache';
        } elseif (stripos($event->getName(), 'media') !== false) {
            $cache = 'JavaScript/CSS cache';
        }

        $this->getEventManager()->dispatch('event_log_info', [
            'group' => 'admin',
            'message' => 'Flushed {cache}.',
            'context' => [
                'cache' => $cache,
            ],
        ]);
    }
}
