<?php

namespace Ryvon\EventLog\Helper;

use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\System\Store;

class StoreViewFinder
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Store
     */
    private $systemStore;

    /**
     * @param RequestInterface $request
     * @param Store $systemStore
     */
    public function __construct(
        RequestInterface $request,
        Store $systemStore
    )
    {
        $this->request = $request;
        $this->systemStore = $systemStore;
    }

    /**
     * @return string|null
     */
    public function getActiveStoreView()
    {
        $storeId = (int)$this->request->getParam('store', 0);
        $websiteId = (int)$this->request->getParam('website', 0);

        if (!$storeId && !$websiteId) {
            if ($this->request->getModuleName() === 'admin' && $this->request->getActionName() === 'save') {
                return 'Default Config';
            }
            return 'All Store Views';
        }

        if ($websiteId) {
            return $this->systemStore->getWebsiteName($websiteId);
        }

        return $this->systemStore->getStoreName($storeId);
    }
}
