<?php

namespace Ryvon\EventLog\Observer\System;

use Ryvon\EventLog\Helper\Group\AdminGroup;
use Ryvon\EventLog\Observer\AbstractEventObserver;
use Magento\Framework\Event;

class CacheFlushFilesObserver extends AbstractEventObserver
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
            'group' => AdminGroup::GROUP_ID,
            'message' => 'Flushed {cache}.',
            'context' => [
                'cache' => $cache,
            ],
        ]);
    }
}
