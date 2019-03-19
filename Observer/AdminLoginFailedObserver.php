<?php

namespace Ryvon\EventLog\Observer;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Ryvon\EventLog\Helper\UserContextHelper;

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

    /*
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
    protected function getUserError($exception)
    {
        if (!$exception || !($exception instanceof \Exception)) {
            return 'unknown error';
        }

        $message = $exception->getMessage();
        $lowerMessage = strtolower(trim($message, '.'));
        switch ($lowerMessage) {
            case 'you did not sign in correctly or your account is temporarily disabled':
                return 'invalid credentials';
            case 'your account is temporarily disabled':
                return 'account is disabled';
            case 'incorrect recaptcha':
            case 'incorrect captcha':
                return 'incorrect captcha';
            default:
                return sprintf('error sent to user: "%s"', $message);
        }
    }
}
