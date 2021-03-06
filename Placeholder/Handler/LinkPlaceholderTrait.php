<?php

namespace Ryvon\EventLog\Placeholder\Handler;

trait LinkPlaceholderTrait
{
    /**
     * @param array $attributes
     * @param string $tag
     * @return string
     */
    public function buildLinkTag($attributes, $tag = 'a'): string
    {
        $content = '';

        if (isset($attributes['text'])) {
            $content = htmlentities($attributes['text'], ENT_QUOTES);
            unset($attributes['text']);
        }

        if (isset($attributes['html'])) {
            $content = $attributes['html'];
            unset($attributes['html']);
        }

        if (!$content) {
            return '';
        }

        $parameters = [];
        foreach ($attributes as $parameter => $value) {
            $parameters[] = sprintf('%s="%s"', htmlentities($parameter, ENT_QUOTES), htmlentities($value, ENT_QUOTES));
        }

        return sprintf('<%s %s>%s</%s>', $tag, implode(' ', $parameters), $content, $tag);
    }
}
