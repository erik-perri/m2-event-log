<?php

namespace Ryvon\EventLog\Helper;

use Ryvon\EventLog\Helper\Placeholder\PlaceholderInterface;
use Magento\Framework\DataObject;

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
    public function replace($message, $context, bool $onlyContext = false): string
    {
        return preg_replace_callback('#\{([^}]+)\}#', function ($matches) use ($context, $onlyContext) {
            // If a placeholder does not exist for this match we will use the string value of the placeholder
            if ($onlyContext || !isset($this->placeholders[$matches[1]])) {
                $value = $context->getData($matches[1]);
                if ($value !== false && $value !== null && $this->canBeString($value)) {
                    return (string)$value;
                }
                return $this->getUnknownText();
            }

            return $this->placeholders[$matches[1]]->getReplaceString($context) ?? $this->getUnknownText();
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
