<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Framework\DataObject;

class UserIpPlaceholder implements PlaceholderInterface
{
    use LinkPlaceholderTrait;

    /**
     * @param DataObject $context
     * @return string|null
     */
    public function getReplaceString($context)
    {
        $userIp = $context->getData('user-ip');
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
