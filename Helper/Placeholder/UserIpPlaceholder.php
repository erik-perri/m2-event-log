<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Framework\DataObject;
use Ryvon\EventLog\Helper\IpLocationHelper;

class UserIpPlaceholder implements PlaceholderInterface
{
    use LinkPlaceholderTrait;

    /**
     * @var IpLocationHelper
     */
    private $locationHelper;

    /**
     * @param IpLocationHelper $locationHelper
     */
    public function __construct(IpLocationHelper $locationHelper)
    {
        $this->locationHelper = $locationHelper;
    }

    /**
     * @return string
     */
    public function getSearchString(): string
    {
        return 'user-ip';
    }

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

        $userIp = htmlentities($userIp, ENT_QUOTES);

        return $this->locationHelper->buildLookupIpTag($userIp, [
            'title' => 'View information for IP: ' . $userIp,
            'target' => '_blank',
        ], $userIp) ?: $userIp;
    }
}
