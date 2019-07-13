<?php

namespace Ryvon\EventLog\Observer\Event;

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
     * Retrieves the event from the observer and passes it to the handle function.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        if (!$event) {
            return;
        }

        $this->handle($event);
    }

    /**
     * Dispatches an add log event if the event context is valid.
     *
     * @param Event $event
     */
    abstract protected function handle(Event $event);

    /**
     * Helper function to retrieve the event manager.
     *
     * @return ManagerInterface
     */
    protected function getEventManager(): ManagerInterface
    {
        return $this->eventManager;
    }
}
