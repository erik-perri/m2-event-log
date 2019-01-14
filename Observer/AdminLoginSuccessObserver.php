<?php

namespace Ryvon\EventLog\Observer;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ryvon\EventLog\Helper\Group\AdminGroup;

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

    /*
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $this->eventManager->dispatch('event_log_info', [
            'group' => AdminGroup::GROUP_ID,
            'message' => 'User logged in.',
        ]);
    }
}
