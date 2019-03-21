<?php

namespace Ryvon\EventLog\Helper\Placeholder;

/**
 * Interface for the placeholders.
 */
interface PlaceholderInterface
{
    /**
     * Returns the search string the placeholder is for (excluding brackets).
     *
     * @return string
     */
    public function getSearchString(): string;

    /**
     * Returns the replacement string of the placeholder.
     *
     * @param \Magento\Framework\DataObject $context
     * @return string|null
     */
    public function getReplaceString($context);
}
