<?php

namespace Ryvon\EventLog\Plugin;

use Ryvon\EventLog\Model\Config;
use Psr\Log\LoggerInterface;
use Ryvon\EventLog\Model\DigestRepository;

class DisableActionValidatorPlugin
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DigestRepository
     */
    private $digestRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    private $auth;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $backendUrl;

    /**
     * @var array
     */
    private $whitelist = [];

    /**
     * @param LoggerInterface $logger
     * @param DigestRepository $digestRepository
     * @param Config $config
     * @param \Magento\Backend\Model\Auth $auth
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param array $whitelist
     */
    public function __construct(
        LoggerInterface $logger,
        DigestRepository $digestRepository,
        Config $config,
        \Magento\Backend\Model\Auth $auth,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        $whitelist = []
    )
    {
        $this->logger = $logger;
        $this->digestRepository = $digestRepository;
        $this->config = $config;
        $this->auth = $auth;
        $this->backendUrl = $backendUrl;
        $this->whitelist = array_merge($this->whitelist, $whitelist, [
            'adminhtml_system_config_edit',
            'adminhtml_user_edit',
            'catalog_category_edit',
            'catalog_product_attribute_edit',
            'catalog_product_set_edit',
            'catalog_product_edit',
            'cms_page_edit',
            'event_log_digest_index',
            'event_log_lookup_ip',
            'sales_order_index',
            'sales_order_view',
        ]);
    }

    /**
     * @param \Magento\Backend\App\AbstractAction $subject
     * @param callable $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     * @return mixed
     */
    public function aroundDispatch(
        /** @noinspection PhpUnusedParameterInspection */
        \Magento\Backend\App\AbstractAction $subject,
        callable $proceed,
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $wasSecretKeyEnabled = $this->backendUrl->useSecretKey();
        $disableSecretKey = $this->shouldDisableSecretKey($request);
        if ($disableSecretKey) {
            $this->backendUrl->turnOffSecretKey();
        }

        $return = $proceed($request);

        if ($wasSecretKeyEnabled && $disableSecretKey) {
            $this->backendUrl->turnOnSecretKey();
        }

        return $return;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    protected function shouldDisableSecretKey(\Magento\Framework\App\RequestInterface $request): bool
    {
        if (!$this->config->getBypassUrlKey()) {
            return false;
        }

        if (!($request instanceof \Magento\Framework\App\Request\Http)) {
            return false;
        }

        if (!$this->auth->isLoggedIn() || $request->isPost()) {
            return false;
        }

        /** @noinspection PhpUndefinedMethodInspection */
        if (!$this->backendUrl->useSecretKey() || $this->backendUrl->getNoSecret()) {
            return false;
        }

        $source = $request->getParam('_source');
        if ($source === null) {
            return false;
        }

        if (!in_array($request->getFullActionName(), $this->whitelist, true)) {
            $this->logger->notice('Action "' . $request->getFullActionName() . '" not in whitelist, not disabling URL key');
            return false;
        }

        if (!$this->digestRepository->getByKey($source)) {
            $this->logger->notice('Digest was not found by key "' . $source . '", not disabling URL key');
            return false;
        }

        return true;
    }
}
