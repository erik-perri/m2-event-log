<?php

namespace Ryvon\EventLog\Observer;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ryvon\EventLog\Helper\StoreViewFinder;
use Psr\Log\LoggerInterface;

abstract class AbstractModificationObserver implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @var StoreViewFinder
     */
    private $storeViewFinder;

    /**
     * @param LoggerInterface $logger
     * @param ManagerInterface $eventManager
     * @param Session $authSession
     * @param StoreViewFinder $storeViewFinder
     */
    public function __construct(
        LoggerInterface $logger,
        ManagerInterface $eventManager,
        Session $authSession,
        StoreViewFinder $storeViewFinder
    )
    {
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->authSession = $authSession;
        $this->storeViewFinder = $storeViewFinder;
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return ManagerInterface
     */
    protected function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * @return string|null
     */
    protected function getActiveStoreView()
    {
        return $this->storeViewFinder->getActiveStoreView();
    }

    /**
     * @param \Magento\Framework\Event $event
     * @return \Magento\Framework\Model\AbstractModel
     */
    abstract protected function getEntity(\Magento\Framework\Event $event);

    /**
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @param $action
     */
    abstract protected function dispatch($entity, $action);

    /*
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            // If we are not logged in to the backend we do not want to log this
            // event.
            if (!$this->authSession->getUser()) {
                return;
            }

            $event = $observer->getEvent();
            if (!$event) {
                return;
            }

            $entity = $this->getEntity($event);
            if (!$entity || !($entity instanceof \Magento\Framework\Model\AbstractModel)) {
                return;
            }

            if ($this->isDeleteEvent($event)) {
                $action = 'deleted';
            } else {
                $action = $entity->isObjectNew() ? 'created' : 'modified';
            }

            $this->dispatch($entity, $action);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @param \Magento\Framework\Event $event
     * @return bool
     */
    protected function isDeleteEvent($event)
    {
        if (!$event || !$event->getName()) {
            return false;
        }

        return preg_match('#_delete_(before|after)$#i', $event->getName()) > 0;
    }
}
