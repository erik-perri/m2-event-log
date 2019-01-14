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
    public function getSearchString()
    {
        return 'user-ip';
    }

    /**
     * @param DataObject $context
     * @return string
     */
    public function getReplaceString($context)
    {
        $userIp = $context->getData('user-ip');
        if (!$userIp) {
            return '';
        }

        return $this->locationHelper->generateLocateLinkTag($userIp, [
            'title' => 'View information for IP: ' . $userIp,
            'target' => '_blank',
            'class' => 'icon',
        ], $userIp) ?: '';
    }
}
