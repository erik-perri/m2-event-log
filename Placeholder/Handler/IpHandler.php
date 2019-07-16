<?php

namespace Ryvon\EventLog\Placeholder\Handler;

use Magento\Framework\DataObject;

class IpHandler implements HandlerInterface
{
    use LinkPlaceholderTrait;

    /**
     * @inheritDoc
     */
    public function handle(DataObject $context)
    {
        $userIp = $context->getData('text');
        if (!$userIp) {
            return null;
        }

        $lookupUrl = $this->getLookupUrl($userIp);
        if (!$lookupUrl) {
            return null;
        }

        return $this->buildLinkTag([
            'text' => $userIp,
            'href' => $lookupUrl,
            'rel' => 'nofollow noindex noopener noreferrer',
            'title' => 'View information for IP: ' . $userIp,
            'target' => '_blank',
        ]);
    }

    /**
     * Checks if the IP is valid and returns the lookup URL if so.
     *
     * @param string $ip
     * @return string|null
     */
    private function getLookupUrl($ip)
    {
        $valid = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        if (!$valid) {
            return null;
        }

        return sprintf('https://tools.keycdn.com/geo?host=%s', $valid);
    }
}
