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
    ) {
        $this->authSession = $authSession;
        $this->remoteAddress = $remoteAddress;
    }

    /**
     * @param array $context
     * @return array
     */
    public function getContextFromCurrentUser($context = []): array
    {
        $user = $this->authSession->getUser();
        if (!$user) {
            if (PHP_SAPI === 'cli') {
                $caller = isset($_SERVER['TERM']) ? 'CLI' : 'Cron';
                return array_merge($context, [
                    'text' => sprintf('%s (%s)', get_current_user(), $caller),
                    'ip-address' => '127.0.0.1',
                ]);
            }

            return $context;
        }

        return $this->getContextFromUser($user, $context);
    }

    /**
     * @param User $user
     * @param array $context
     * @return array
     */
    public function getContextFromUser($user, $context = []): array
    {
        if (!$user) {
            return $context;
        }

        return array_merge($context, [
            'text' => $user->getUserName(),
            'id' => $user->getId(),
            'ip-address' => $this->getClientIp(),
        ]);
    }

    /**
     * @return string
     */
    public function getClientIp(): string
    {
        $ip = $this->remoteAddress->getRemoteAddress();
        if ($this->isValidIp($ip)) {
            return $ip;
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
            foreach ($ips as $test) {
                if ($this->isValidIp($test)) {
                    return $test;
                }
            }
        }

        if (isset($_SERVER['HTTP_X_REAL_IP']) && $this->isValidIp($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        }

        return $ip;
    }

    /**
     * @param string $ip
     * @return bool
     */
    private function isValidIp($ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) ? true : false;
    }
}
