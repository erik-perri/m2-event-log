<?php

namespace Ryvon\EventLog\Plugin;

use Ryvon\EventLog\Helper\UrlKeyDisableChecker;
use Magento\Backend\App\Request\BackendValidator;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;

class DisableBackendValidatorPlugin
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
     * @param UrlKeyDisableChecker $keyDisableChecker
     * @param UrlInterface $backendUrl
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
     * @param BackendValidator $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @param ActionInterface $action
     * @return mixed
     */
    public function aroundValidate(
        /** @noinspection PhpUnusedParameterInspection */
        BackendValidator $subject,
        callable $proceed,
        RequestInterface $request,
        ActionInterface $action
    )
    {
        $wasSecretKeyEnabled = $this->backendUrl->useSecretKey();
        $disableSecretKey = $this->keyDisableChecker->shouldDisableSecretKey($request);

        if ($disableSecretKey) {
            $this->backendUrl->turnOffSecretKey();
        }

        $return = $proceed($request, $action);

        if ($wasSecretKeyEnabled && $disableSecretKey) {
            $this->backendUrl->turnOnSecretKey();
        }

        return $return;
    }
}
