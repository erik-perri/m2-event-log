<?php

namespace Ryvon\EventLog\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Base class for log observers that are monitoring for a generic event.
 */
abstract class AbstractEventObserver implements ObserverInterface
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
     * @return ManagerInterface
     */
    public function getEventManager(): ManagerInterface
    {
        return $this->eventManager;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        if (!$event) {
            return;
        }

        $this->dispatch($event);
    }

    /**
     * @param Event $event
     */
    abstract protected function dispatch(Event $event);
}
