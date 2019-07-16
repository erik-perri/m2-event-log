<?php

namespace Ryvon\EventLog\Helper\Placeholder;

/**
 * Interface for the placeholders.
 */
interface PlaceholderInterface
{
    /**
     * Returns the replacement string of the placeholder.
     *
     * @param \Magento\Framework\DataObject $context
     * @return string|null
     */
    public function getReplaceString($context);
}
