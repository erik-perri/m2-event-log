<?php

namespace Ryvon\EventLog\Helper;

use Magento\Framework\DataObject;
use Ryvon\EventLog\Helper\Placeholder\PlaceholderInterface;

class PlaceholderReplacer
{
    /**
     * @var PlaceholderInterface[]
     */
    private $placeholders = [];

    /**
     * @param array $placeholders
     */
    public function __construct($placeholders = [])
    {
        foreach ($placeholders as $placeholder) {
            if ($placeholder instanceof PlaceholderInterface) {
                $this->addPlaceholder($placeholder);
            }
        }
    }

    /**
     * @param PlaceholderInterface $placeholder
     * @return PlaceholderReplacer
     */
    public function addPlaceholder(PlaceholderInterface $placeholder): PlaceholderReplacer
    {
        $this->placeholders[$placeholder->getSearchString()] = $placeholder;
        return $this;
    }

    /**
     * @param string $message
     * @param DataObject $context
     * @return string
     */
    public function replace($message, $context): string
    {
        $unknownText = '[Unknown]';

        return preg_replace_callback('#\{([^}]+)\}#', function ($matches) use ($context, $unknownText) {
            // If a placeholder does not exist for this match we wil
            if (!isset($this->placeholders[$matches[1]])) {
                $value = $context->getData($matches[1]);
                if ($value !== false && $value !== null && $this->canBeString($value)) {
                    return $value;
                }
                return $unknownText;
            }

            return $this->placeholders[$matches[1]]->getReplaceString($context) ?? $unknownText;
        }, $message);
    }

    /**
     * @param $value
     * @return bool
     */
    protected function canBeString($value): bool
    {
        // https://stackoverflow.com/a/5496674
        return !is_array($value) && (
                (!is_object($value) && settype($value, 'string') !== false) ||
                (is_object($value) && method_exists($value, '__toString'))
            );
    }
}
