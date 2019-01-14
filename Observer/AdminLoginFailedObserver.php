<?php

namespace Ryvon\EventLog\Observer;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Ryvon\EventLog\Helper\Group\AdminGroup;

class AdminLoginFailedObserver implements ObserverInterface
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @param ManagerInterface $eventManager
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(ManagerInterface $eventManager, RemoteAddress $remoteAddress)
    {
        $this->eventManager = $eventManager;
        $this->remoteAddress = $remoteAddress;
    }

    /*
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $userName = $observer->getData('user_name') ?: '';
        $message = $this->getUserError($observer->getData('exception'));

        $this->eventManager->dispatch('event_log_security', [
            'group' => AdminGroup::GROUP_ID,
            'message' => 'User login failed, {error}.',
            'context' => [
                'user-name' => $userName,
                'user-ip' => $this->remoteAddress->getRemoteAddress(),
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
