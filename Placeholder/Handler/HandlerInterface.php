<?php

namespace Ryvon\EventLog\Placeholder\Handler;

use Magento\Framework\DataObject;

interface HandlerInterface
{
    /**
     * Renders the placeholder.
     *
     * @param DataObject $context
     * @return string|null
     */
    public function handle(DataObject $context);
}
