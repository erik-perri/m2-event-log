<?php

namespace Ryvon\EventLog\Observer\Action;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\ManagerInterface;

/**
 * Monitors for the selective cache refresh action.
 */
class MassCacheRefreshObserver implements ActionObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param ManagerInterface $eventManager
     */
    public function __construct(ManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }

    /**
     * @inheritDoc
     */
    public function handle(Http $request)
    {
        $types = $request->get('types');
        if (!$types) {
            return;
        }

        $types = explode(',', $types);

        $this->eventManager->dispatch('event_log_info', [
            'group' => 'admin',
            'message' => 'Flushed cache(s) {types}.',
            'context' => [
                'types' => implode(', ', $types),
            ],
        ]);
    }
}
