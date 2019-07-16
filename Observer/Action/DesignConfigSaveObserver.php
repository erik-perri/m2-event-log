<?php

namespace Ryvon\EventLog\Observer\Action;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\ManagerInterface;

/**
 * Monitors for the design config save request.
 */
class DesignConfigSaveObserver implements ActionObserverInterface
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
        $scope = $request->get('scope');
        $scopeId = $request->get('scope_id');

        $this->eventManager->dispatch('event_log_info', [
            'group' => 'admin',
            'message' => 'Design configuration scope {design-config} modified.',
            'context' => [
                'design-config' => [
                    'handler' => 'design-config',
                    'text' => $scope,
                    'id' => $scopeId,
                ],
            ],
        ]);
    }
}
