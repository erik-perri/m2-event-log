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
     * Generates an anchor tag to the lookup IP info URL.
     *
     * @param string $ip
     * @param array $attributes
     * @param string $innerHtml
     * @param string $tag
     * @return string|null
     */
    public function generateLocateLinkTag($ip, $attributes, $innerHtml, $tag = 'a')
    {
        $url = $this->getLocateUrl($ip);
        if (!$url) {
            return null;
        }

        return $this->buildTag($tag, $attributes, $innerHtml, [
            'rel' => 'nofollow noindex noopener noreferrer',
            'href' => $url,
        ]);
    }

    /**
     * Checks if the IP is valid and returns the lookup URL if so.
     *
     * @param string $ip
     * @return string|null
     */
    private function getLocateUrl($ip)
    {
        $valid = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        if (!$valid) {
            return null;
        }

        return $this->urlBuilder->getUrl('event_log/lookup/ip', ['v' => $valid]);
    }

    /**
     * Builds a tag with the specified properties.
     *
     * @param string $tag
     * @param array $attributes
     * @param string $innerHtml
     * @param array $defaultAttributes
     * @return string
     */
    private function buildTag(string $tag, array $attributes, string $innerHtml, array $defaultAttributes = []): string
    {
        foreach ($defaultAttributes as $key => $defaultValue) {
            if (!array_key_exists($key, $attributes)) {
                $attributes[$key] = $defaultValue;
            }
        }

        $parameters = [];
        foreach ($attributes as $parameter => $value) {
            $parameters[] = sprintf('%s="%s"', htmlentities($parameter, ENT_QUOTES), htmlentities($value, ENT_QUOTES));
        }

        return sprintf('<%s %s>%s</%s>', $tag, implode(' ', $parameters), $innerHtml, $tag);
    }
}
