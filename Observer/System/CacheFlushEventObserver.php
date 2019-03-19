<?php

namespace Ryvon\EventLog\Observer\System;

use Ryvon\EventLog\Observer\AbstractEventObserver;
use Magento\Framework\Event;

class CacheFlushEventObserver extends AbstractEventObserver
{
    /**
     * @param Event $event
     */
    protected function dispatch(Event $event)
    {
        $cache = 'unknown cache';
        if (preg_match('#_all$#i', $event->getName())) {
            $cache = 'cache storage';
        } else if (preg_match('#_system$#i', $event->getName())) {
            $cache = 'Magento cache';
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
