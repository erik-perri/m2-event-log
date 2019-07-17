<?php

namespace Ryvon\EventLog\Observer\Event;

use Exception;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ryvon\EventLog\Helper\UserContextHelper;

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
    ) {
        $this->eventManager = $eventManager;
        $this->userContextHelper = $userContextHelper;
    }

    /**
     * Adds an event log when the user fails to login to the admin.
     *
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
                'error' => $message,
            ],
            'user-context' => [
                'text' => $userName,
                'ip-address' => $this->userContextHelper->getClientIp(),
            ],
        ]);
    }

    /**
     * Retrieves a simplified version of the login error.
     *
     * @param Exception $exception
     * @return string
     */
    private function getUserError($exception)
    {
        if (!$exception || !($exception instanceof Exception)) {
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
