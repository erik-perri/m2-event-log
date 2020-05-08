<?php

namespace Ryvon\EventLog\Placeholder;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Ryvon\EventLog\Placeholder\Handler\HandlerInterface;

/**
 * Replaces placeholders in the message string.
 */
class PlaceholderProcessor
{
    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var Handler\HandlerInterface[]
     */
    private $handlers;

    /**
     * @param DataObjectFactory $dataObjectFactory
     * @param array $handlers
     */
    public function __construct(
        DataObjectFactory $dataObjectFactory,
        $handlers = []
    ) {
        $this->dataObjectFactory = $dataObjectFactory;
        $this->handlers = $handlers;
    }

    /**
     * Returns the string to use when a placeholder is not able to be replaced.
     *
     * @return string
     */
    public function getUnknownText(): string
    {
        return htmlentities(__('[Unknown]'));
    }

    /**
     * Replaces any placeholders in the message.
     *
     * @param string $message
     * @param DataObject|array $context
     * @param bool $withoutHandlers
     * @return string
     */
    public function process(string $message, $context, bool $withoutHandlers = false): string
    {
        if (!($context instanceof DataObject)) {
            $context = $this->dataObjectFactory->create(['data' => $context]);
        }

        $message = htmlentities($message, ENT_QUOTES);

        return preg_replace_callback('#\{([^}]+)\}#', function ($matches) use ($context, $withoutHandlers) {
            $placeholderKey = $matches[1];

            $placeholderValue = $context->getData($placeholderKey);
            if ($placeholderValue === null) {
                return $this->getUnknownText();
            }

            $placeholderDataObject = $this->createPlaceholderContext($placeholderKey, $placeholderValue);

            return $this->handle($placeholderDataObject, $withoutHandlers);
        }, $message);
    }

    /**
     * Returns the replacement string for the specified placeholder key using the specified context.
     *
     * @param DataObject|array $context
     * @param bool $withoutHandlers
     * @return string
     */
    private function handle($context, bool $withoutHandlers = false): string
    {
        if (!($context instanceof DataObject)) {
            $context = $this->dataObjectFactory->create(['data' => $context]);
        }

        $plainTextVersion = $context->getData('text');
        if ($plainTextVersion === null || !$this->canBeString($plainTextVersion)) {
            return $this->getUnknownText();
        }

        $plainTextVersion = htmlentities(__((string)$plainTextVersion));

        $placeholderHandlerId = $context->getData('handler');
        $placeholderHandler = $placeholderHandlerId ? ($this->handlers[$placeholderHandlerId] ?? null) : null;
        if ($withoutHandlers || !$placeholderHandler || !($placeholderHandler instanceof HandlerInterface)) {
            return $plainTextVersion;
        }

        $value = $placeholderHandler->handle($context);
        if ($value === null) {
            return $plainTextVersion;
        }

        return $value;
    }

    /**
     * Creates the placeholder context.
     *
     * If the value is not an array it is set as the context's 'text' value.  If no handler is specified the placeholder
     * key is used.
     *
     * @param string $placeholderKey
     * @param string|array $placeholderValue
     * @return DataObject
     */
    private function createPlaceholderContext(string $placeholderKey, $placeholderValue): DataObject
    {
        if (!is_array($placeholderValue)) {
            $placeholderContext = [
                'text' => $placeholderValue,
            ];
        } else {
            $placeholderContext = $placeholderValue;
        }

        if (!array_key_exists('handler', $placeholderContext)) {
            $placeholderContext['handler'] = $placeholderKey;
        }

        return $this->dataObjectFactory->create([
            'data' => $placeholderContext,
        ]);
    }

    /**
     * Checks if the value can be properly converted to a plain string.
     *
     * @param mixed $value
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
