<?php

namespace Ryvon\EventLog\Placeholder;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Ryvon\EventLog\Placeholder\Handler\HandlerInterface;

class PlaceholderProcessor
{
    /**
     * @var string
     */
    private $unknownText = '[Unknown]';

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var Handler\HandlerInterface[]
     */
    private $handlers = [];

    /**
     * @param DataObjectFactory $dataObjectFactory
     * @param array $handlers
     */
    public function __construct(DataObjectFactory $dataObjectFactory, $handlers = [])
    {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->handlers = $handlers;
    }

    /**
     * @return string
     */
    public function getUnknownText(): string
    {
        return $this->unknownText;
    }

    /**
     * @param string $message
     * @param DataObject $context
     * @param bool $plainText
     * @return string
     */
    public function process(string $message, DataObject $context, bool $plainText = false): string
    {
        $message = htmlentities($message, ENT_QUOTES);

        return preg_replace_callback('#\{([^}]+)\}#', function ($matches) use ($context, $plainText) {
            $placeholderKey = $matches[1];

            $placeholderContext = $context->getData($placeholderKey);
            if (!is_array($placeholderContext)) {
                if (!$this->canBeString($placeholderContext)) {
                    return $this->unknownText;
                }

                $placeholderContext = ['text' => (string)$placeholderContext];

                switch($placeholderKey) {
                    case 'user-ip':
                        $placeholderContext['handler'] = 'ip';
                        break;
                }
            }

            $plainTextVersion = $placeholderContext['text'] ?? $this->unknownText;
            if (!$this->canBeString($plainTextVersion)) {
                return $this->unknownText;
            }

            $placeholderHandlerId = $placeholderContext['handler'] ?? null;
            $placeholderHandler = $placeholderHandlerId ? ($this->handlers[$placeholderHandlerId] ?? null) : null;
            if ($plainText || !$placeholderHandler || !($placeholderHandler instanceof HandlerInterface)) {
                return htmlentities($plainTextVersion);
            }

            $value = $placeholderHandler->handle($this->dataObjectFactory->create(['data' => $placeholderContext]));
            if ($value === null) {
                return htmlentities($plainTextVersion);
            }

            return $value;
        }, $message);
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
}
