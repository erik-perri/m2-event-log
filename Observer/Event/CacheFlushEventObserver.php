<?php

namespace Ryvon\EventLog\Observer\Event;

use Magento\Framework\Event;

/**
 * Handles the flush cache events.
 */
class CacheFlushEventObserver extends AbstractEventObserver
{
    /**
     * @inheritDoc
     */
    protected function handle(Event $event)
    {
        $cache = 'unknown cache';
        if (preg_match('#_all$#i', $event->getName())) {
            $cache = 'cache storage';
        } elseif (preg_match('#_system$#i', $event->getName())) {
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
