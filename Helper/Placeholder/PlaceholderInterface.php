<?php

namespace Ryvon\EventLog\Helper\Placeholder;

interface PlaceholderInterface
{
    /**
     * @return string
     */
    public function getSearchString(): string;

    /**
     * @param \Magento\Framework\DataObject $context
     * @return string|null
     */
    public function getReplaceString($context);
}
