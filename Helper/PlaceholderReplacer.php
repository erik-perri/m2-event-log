<?php

namespace Ryvon\EventLog\Helper;

use Magento\Framework\DataObject;
use Ryvon\EventLog\Helper\Placeholder\PlaceholderInterface;

class PlaceholderReplacer
{
    /**
     * @var string
     */
    private $unknownText = '[Unknown]';

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
     * @param bool $onlyContext
     * @return string
     */
    public function replace(string $message, DataObject $context, bool $onlyContext = false): string
    {
        $message = htmlentities($message, ENT_QUOTES);

        return preg_replace_callback('#\{([^}]+)\}#', function ($matches) use ($context, $onlyContext) {
            // If a placeholder does not exist for this match we will use the string value of the placeholder
            if ($onlyContext || !isset($this->placeholders[$matches[1]])) {
                return $this->getReplaceStringFromContext($matches[1], $context);
            }

            $value = $this->placeholders[$matches[1]]->getReplaceString($context);
            if ($value === null) {
                return $this->getReplaceStringFromContext($matches[1], $context);
            }

            return $value;
        }, $message);
    }

    /**
     * Returns the replace string from the context, using unknown if not found.
     *
     * HTML in the string is escaped.
     *
     * @param string $placeholderKey
     * @param DataObject $context
     * @return string
     */
    private function getReplaceStringFromContext(string $placeholderKey, DataObject $context): string
    {
        $value = $context->getData($placeholderKey);
        if ($value !== false && $value !== null && $this->canBeString($value)) {
            return htmlentities((string)$value, ENT_QUOTES);
        }
        return htmlentities($this->getUnknownText(), ENT_QUOTES);
    }

    /**
     * @param $value
     * @return bool
     */
    private function canBeString($value): bool
    {
        // https://stackoverflow.com/a/5496674
        return !is_array($value) && (
                (!is_object($value) && settype($value, 'string') !== false) ||
                (is_object($value) && method_exists($value, '__toString'))
            );
    }

    /**
     * @return string
     */
    public function getUnknownText(): string
    {
        return $this->unknownText;
    }

    /**
     * @param string $unknownText
     * @return PlaceholderReplacer
     */
    public function setUnknownText(string $unknownText): PlaceholderReplacer
    {
        $this->unknownText = $unknownText;
        return $this;
    }
}
