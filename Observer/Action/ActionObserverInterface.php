<?php

namespace Ryvon\EventLog\Observer\Action;

use Magento\Framework\App\Request\Http;

/**
 * Interface for the classes used by ActionMonitorPlugin to monitor requests.
 */
interface ActionObserverInterface
{
    /**
     * Called when the request matches the monitored action.
     *
     * Should create an event log entry if the request contains the expected context.
     *
     * @param Http $request
     * @return void
     */
    public function handle(Http $request);
}
