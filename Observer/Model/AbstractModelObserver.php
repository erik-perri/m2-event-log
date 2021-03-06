<?php

namespace Ryvon\EventLog\Observer\Model;

use Exception;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Model\AbstractModel;
use Psr\Log\LoggerInterface;
use Ryvon\EventLog\Helper\StoreViewFinder;

/**
 * Base class for log observers that are monitoring for a model modification event.
 */
abstract class AbstractModelObserver implements ObserverInterface
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
    ) {
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->authSession = $authSession;
        $this->storeViewFinder = $storeViewFinder;
    }

    /**
     * Ensures we are a valid event with an expected model using getModel then calls handle.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            // If we are not logged in to the backend we do not want to log this event.
            if (!$this->authSession->getUser()) {
                return;
            }

            $event = $observer->getEvent();
            if (!$event) {
                return;
            }

            $model = $this->findModel($event);
            if (!$model || !($model instanceof AbstractModel)) {
                return;
            }

            if ($this->isDeleteEvent($event)) {
                $action = 'deleted';
            } else {
                $action = $model->isObjectNew() ? 'created' : 'modified';
            }

            $this->handle($model, $action);
        } catch (Exception $e) {
            $this->getLogger()->critical($e);
        }
    }

    /**
     * Called by execute to obtain the model from the event. Should return null if the model is not what was expected.
     *
     * @param Event $event
     * @return AbstractModel|null
     */
    abstract protected function findModel(Event $event): ?AbstractModel;

    /**
     * Dispatches an add log event if the model is valid.
     *
     * @param AbstractModel $entity
     * @param string $action
     */
    abstract protected function handle(AbstractModel $entity, string $action);

    /**
     * Checks if the event is a delete event.
     *
     * @param Event|null $event
     * @return bool
     */
    private function isDeleteEvent($event): bool
    {
        if (!$event || !$event->getName()) {
            return false;
        }

        return preg_match('#_delete_(before|after)$#i', $event->getName()) > 0;
    }

    /**
     * Helper function to retrieve the logger.
     *
     * @return LoggerInterface
     */
    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Helper function to retrieve the event manager.
     *
     * @return ManagerInterface
     */
    protected function getEventManager(): ManagerInterface
    {
        return $this->eventManager;
    }

    /**
     * Helper function to retrieve the active store view.
     *
     * @return string|null
     */
    protected function getActiveStoreView()
    {
        return $this->storeViewFinder->getActiveStoreView();
    }
}
