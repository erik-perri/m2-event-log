<?php

namespace Ryvon\EventLog\Helper;

use Magento\Backend\Model\UrlInterface;

class IpLocationHelper
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     */
    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param $ip
     * @param array $attributes
     * @param string $child
     * @param string $tag
     * @return string|null
     */
    public function generateLocateLinkTag($ip, $attributes, $child, $tag = 'a')
    {
        $url = $this->getLocateUrl($ip);
        if (!$url) {
            return null;
        }

        // I don't know if email clients will attempt to visit links from the digest, can't hurt...
        if (!isset($attributes['rel'])) {
            $attributes['rel'] = 'nofollow noindex noopener noreferrer';
        }
        if (!isset($attributes['href'])) {
            $attributes['href'] = $url;
        }

        $parameters = [];
        foreach ($attributes as $parameter => $value) {
            $parameters[] = sprintf('%s="%s"', htmlentities($parameter, ENT_QUOTES), htmlentities($value, ENT_QUOTES));
        }

        return sprintf('<%s %s>%s</%s>', $tag, implode(' ', $parameters), $child, $tag);
    }

    /**
     * @param string $ip
     * @return string|null
     */
    public function getLocateUrl($ip)
    {
        $valid = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        if (!$valid) {
            return null;
        }

        return $this->urlBuilder->getUrl('event_log/lookup/ip', ['v' => $valid]);
    }
}
