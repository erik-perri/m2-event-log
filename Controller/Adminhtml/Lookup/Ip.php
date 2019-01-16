<?php

namespace Ryvon\EventLog\Controller\Adminhtml\Lookup;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;

class Ip extends Action
{
    /**
     * @return ResultInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        $ip = $this->getIpAddress();
        if (!$ip) {
            throw new NotFoundException(__('Invalid IP'));
        }

        return $this->createRedirect('https://tools.keycdn.com/geo?host=' . $ip);
    }

    /**
     * @param string $url
     * @return ResultInterface
     */
    protected function createRedirect($url)
    {
        /**
         * @var Redirect $resultRedirect
         */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($url);
        $resultRedirect->setHeader('Referrer-Policy', 'no-referrer');

        return $resultRedirect;
    }

    /**
     * @return bool|string
     */
    protected function getIpAddress()
    {
        $ip = $this->getRequest()->getParam('v');
        if ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return $ip;
        }

        return false;
    }
}
