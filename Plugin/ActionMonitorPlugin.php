<?php

namespace Ryvon\EventLog\Plugin;

use Magento\Backend\App\AbstractAction;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Ryvon\EventLog\Observer\Action\ActionObserverInterface;

/**
 * Plugin to monitor for specific actions to be handled by ActionObserverInterface instances.
 */
class ActionMonitorPlugin
{
    /**
     * @var array
     */
    private $monitors;

    /**
     * @param array $monitors
     */
    public function __construct($monitors = [])
    {
        $this->monitors = $monitors;
    }

    /**
     * Calls the handle function of any ActionObserverInterface found for the current request's getFullActionName.
     *
     * @param AbstractAction $subject
     * @param RequestInterface $request
     * @return array
     */
    public function beforeDispatch(
        /** @noinspection PhpUnusedParameterInspection */ AbstractAction $subject,
        RequestInterface $request
    ): array {
        if (($request instanceof Http) && $request->isPost()) {
            $action = $request->getFullActionName();
            $monitor = $this->monitors[$action] ?? null;
            if ($monitor && $monitor instanceof ActionObserverInterface) {
                $monitor->handle($request);
            }
        }

        return [$request];
    }
}
