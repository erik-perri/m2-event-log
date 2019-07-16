<?php

namespace Ryvon\EventLog\Plugin;

use Exception;
use Magento\Backend\App\AbstractAction;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;
use Ryvon\EventLog\Observer\Action\ActionObserverInterface;

/**
 * Plugin to monitor for specific actions to be handled by ActionObserverInterface instances.
 */
class ActionMonitorPlugin
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $monitors;

    /**
     * @param LoggerInterface $logger
     * @param array $monitors
     */
    public function __construct(LoggerInterface $logger, $monitors = [])
    {
        $this->logger = $logger;
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
        try {
            if (($request instanceof Http) && $request->isPost()) {
                $action = $request->getFullActionName();
                $monitor = $this->monitors[$action] ?? null;
                if ($monitor && $monitor instanceof ActionObserverInterface) {
                    $monitor->handle($request);
                }
            }
        } catch (Exception $e) {
            $this->logger->critical($e);
        }

        return [$request];
    }
}
