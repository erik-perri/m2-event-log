<?php

namespace Ryvon\EventLog\Observer\System;

use Ryvon\EventLog\Observer\AbstractEventObserver;
use Magento\Framework\Event;

class CacheFlushFilesEventObserver extends AbstractEventObserver
{
    /**
     * @param Event $event
     */
    protected function dispatch(Event $event)
    {
        $cache = 'unknown files cache';
        if (stripos($event->getName(), 'catalog_images') !== false) {
            $cache = 'catalog images cache';
        } else if (stripos($event->getName(), 'static_files') !== false) {
            $cache = 'static files cache';
        } else if (stripos($event->getName(), 'media') !== false) {
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
