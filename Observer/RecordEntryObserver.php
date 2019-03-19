<?php

namespace Ryvon\EventLog\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\User\Model\User;
use Psr\Log\LoggerInterface;
use Ryvon\EventLog\Helper\DigestHelper;
use Ryvon\EventLog\Helper\Group\AdminGroup;
use Ryvon\EventLog\Helper\UserContextHelper;

class RecordEntryObserver implements ObserverInterface
{
    /**
     * @var DigestHelper
     */
    private $helper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UserContextHelper
     */
    private $userContextHelper;

    /**
     * @param DigestHelper $helper
     * @param LoggerInterface $logger
     * @param UserContextHelper $userContextHelper
     */
    public function __construct(
        DigestHelper $helper,
        LoggerInterface $logger,
        UserContextHelper $userContextHelper
    )
    {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->userContextHelper = $userContextHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $level = $this->getEntryLevelFromEventName($observer->getEvent());
            if (!$level) {
                return;
            }

            $message = $observer->getData('message');
            if ($message) {
                $group = $observer->getData('group') ?: '';
                $context = $observer->getData('context') ?: [];

                $user = $observer->getData('user');
                if ($group === AdminGroup::GROUP_ID || $observer->getData('user')) {
                    if ($user instanceof User) {
                        $context = $this->userContextHelper->getContextFromUser($user, $context);
                    } else {
                        $context = $this->userContextHelper->getContextFromCurrentUser($context);
                    }
                }

                $this->helper->addEntry(null, $level, $group, $message, $context);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @param \Magento\Framework\Event $event
     * @return string|null
     */
    protected function getEntryLevelFromEventName($event)
    {
        if (!$event || !$event->getName()) {
            return null;
        }

        $match = '#^event_log_([a-z]+)$#i';
        if (!preg_match($match, $event->getName())) {
            return null;
        }

        return preg_replace('#^event_log_([a-z]+)$#i', '$1', $event->getName());
    }
}
