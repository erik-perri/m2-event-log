<?php

namespace Ryvon\EventLog\Controller\Adminhtml\Lookup;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * IP lookup redirect controller.
 */
class Ip extends Action
{
    /**
     * Redirect the user to the IP lookup URL.
     *
     * @return ResultInterface
     * @throws NotFoundException
     */
    public function execute(): ResultInterface
    {
        $ip = $this->getIpAddress();
        if (!$ip) {
            throw new NotFoundException(__('Invalid IP'));
        }

        return $this->createRedirect('https://tools.keycdn.com/geo?host=' . $ip);
    }

    /**
     * Retrieves the IP address from the request. Returns null if IP does not validate or is private.
     *
     * @return string|null
     */
    protected function getIpAddress()
    {
        $ip = $this->getRequest()->getParam('v');
        if ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return $ip;
        }

        return null;
    }

    /**
     * Creates the redirect result.
     *
     * @param string $url
     * @return ResultInterface
     */
    protected function createRedirect($url): ResultInterface
    {
        /**
         * @var Redirect $resultRedirect
         */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($url);
        $resultRedirect->setHeader('Referrer-Policy', 'no-referrer');

        return $resultRedirect;
    }
}
