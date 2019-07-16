<?php

namespace Ryvon\EventLog\Observer\Action;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\ManagerInterface;
use Ryvon\EventLog\Helper\StoreViewFinder;

/**
 * Monitors for the config save request.
 */
class SystemConfigSaveObserver implements ActionObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var StoreViewFinder
     */
    private $storeViewFinder;

    /**
     * @param ManagerInterface $eventManager
     * @param StoreViewFinder $storeViewFinder
     */
    public function __construct(
        ManagerInterface $eventManager,
        StoreViewFinder $storeViewFinder
    ) {
        $this->eventManager = $eventManager;
        $this->storeViewFinder = $storeViewFinder;
    }

    /**
     * @inheritDoc
     */
    public function handle(Http $request)
    {
        $section = $request->get('section');
        if (!$section) {
            return;
        }

        $this->eventManager->dispatch('event_log_info', [
            'group' => 'admin',
            'message' => 'Configuration section {config-section} modified.',
            'context' => [
                'config-section' => [
                    'handler' => 'config-section',
                    'text' => $section,
                ],
                'store-view' => $this->storeViewFinder->getActiveStoreView(),
            ],
        ]);
    }
}
