<?php

namespace Ryvon\EventLog\Helper;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\User\Model\User;

class UserContextHelper
{
    /**
     * @var Session
     */
    private $authSession;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @param Session $authSession
     * @param RemoteAddress $remoteAddress
     */
    public function __construct(
        Session $authSession,
        RemoteAddress $remoteAddress
    )
    {
        $this->authSession = $authSession;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * @param User $user
     * @param array $context
     * @return array
     */
    public function getContextFromUser($user, $context = [])
    {
        if (!$user) {
            return $context;
        }

        return array_merge($context, [
            'user-id' => $user->getId(),
            'user-name' => $user->getUserName(),
            'user-ip' => $this->remoteAddress->getRemoteAddress(),
        ]);
    }

    /**
     * @param array $context
     * @return array
     */
    public function getContextFromCurrentUser($context = [])
    {
        $user = $this->authSession->getUser();
        if (!$user) {
            return $context;
        }

        return $this->getContextFromUser($user, $context);
    }
}
