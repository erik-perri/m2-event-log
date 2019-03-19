<?php

namespace Ryvon\EventLog\Plugin;

use Ryvon\EventLog\Helper\UrlKeyDisableChecker;
use Magento\Backend\App\AbstractAction;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\RequestInterface;

class DisableActionValidatorPlugin
{
    /**
     * @var UrlInterface
     */
    private $backendUrl;

    /**
     * @var UrlKeyDisableChecker
     */
    private $keyDisableChecker;

    /**
     * @param UrlInterface $backendUrl
     * @param UrlKeyDisableChecker $keyDisableChecker
     */
    public function __construct(
        UrlInterface $backendUrl,
        UrlKeyDisableChecker $keyDisableChecker
    )
    {
        $this->backendUrl = $backendUrl;
        $this->keyDisableChecker = $keyDisableChecker;
    }

    /**
     * @param AbstractAction $subject
     * @param callable $proceed
     * @param RequestInterface $request
     * @return mixed
     */
    public function aroundDispatch(
        /** @noinspection PhpUnusedParameterInspection */
        AbstractAction $subject,
        callable $proceed,
        RequestInterface $request
    )
    {
        $wasSecretKeyEnabled = $this->backendUrl->useSecretKey();
        $disableSecretKey = $this->keyDisableChecker->shouldDisableSecretKey($request);

        if ($disableSecretKey) {
            $this->backendUrl->turnOffSecretKey();
        }

        $return = $proceed($request);

        if ($wasSecretKeyEnabled && $disableSecretKey) {
            $this->backendUrl->turnOnSecretKey();
        }

        return $return;
    }
}
