<?php

namespace Ryvon\EventLog\Observer;

use Ryvon\EventLog\Helper\UserContextHelper;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Monitors for the admin login failed event.
 */
class AdminLoginFailedObserver implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var UserContextHelper
     */
    private $userContextHelper;

    /**
     * @param ManagerInterface $eventManager
     * @param UserContextHelper $userContextHelper
     */
    public function __construct(
        ManagerInterface $eventManager,
        UserContextHelper $userContextHelper
    )
    {
        $this->eventManager = $eventManager;
        $this->userContextHelper = $userContextHelper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $userName = $observer->getData('user_name') ?: '';
        $message = $this->getUserError($observer->getData('exception'));

        $this->eventManager->dispatch('event_log_security', [
            'group' => 'admin',
            'message' => 'User login failed, {error}.',
            'context' => [
                'user-name' => $userName,
                'user-ip' => $this->userContextHelper->getClientIp(),
                'error' => $message,
            ],
        ]);
    }

    /**
     * @param \Exception $exception
     * @return string
     */
    private function getUserError($exception)
    {
        if (!$exception || !($exception instanceof \Exception)) {
            return 'unknown error';
        }

        $message = $exception->getMessage();

        if (preg_match('#(did not sign in correctly|account sign-in was incorrect)#i', $message)) {
            return 'invalid credentials';
        }

        $lowerMessage = strtolower(trim($message, '.'));
        switch ($lowerMessage) {
            case 'your account is temporarily disabled':
                return 'account is disabled';
            case 'incorrect recaptcha':
            case 'incorrect captcha':
                return 'incorrect captcha';
            default:
                return sprintf('error sent to user: "%s"', trim($message, '.'));
        }
    }
}
