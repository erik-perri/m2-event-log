<?php

namespace Ryvon\EventLog\Observer\Event;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Monitors for the admin login success event.
 */
class AdminLoginSuccessObserver implements ObserverInterface
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
     * Adds an event log when a user logs into the admin.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $this->eventManager->dispatch('event_log_info', [
            'group' => 'admin',
            'message' => 'User logged in.',
        ]);
    }
}
