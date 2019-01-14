<?php

namespace Ryvon\EventLog\Helper\Placeholder;

interface PlaceholderInterface
{
    /**
     * @return string
     */
    public function getSearchString();

    /**
     * @param \Magento\Framework\DataObject $context
     * @return string
     */
    public function getReplaceString($context);
}
