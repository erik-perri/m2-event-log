<?php

namespace Ryvon\EventLog\Block\Adminhtml\Digest;

use Magento\Backend\Block\Template;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Ryvon\EventLog\Helper\DigestRequestHelper;
use Ryvon\EventLog\Helper\IpLocationHelper;
use Ryvon\EventLog\Helper\PlaceholderReplacer;

class OrderBlock extends EntryBlock
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var IpLocationHelper
     */
    private $locationHelper;

    /**
     * @var PricingHelper
     */
    private $priceHelper;

    /**
     * @param UrlInterface $urlBuilder
     * @param IpLocationHelper $locationHelper
     * @param PricingHelper $priceHelper
     * @param DigestRequestHelper $digestRequestHelper
     * @param PlaceholderReplacer $placeholderReplacer
     * @param Timezone $timezone
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        UrlInterface $urlBuilder,
        IpLocationHelper $locationHelper,
        PricingHelper $priceHelper,
        DigestRequestHelper $digestRequestHelper,
        PlaceholderReplacer $placeholderReplacer,
        Timezone $timezone,
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($digestRequestHelper, $placeholderReplacer, $timezone, $context, $data);

        $this->urlBuilder = $urlBuilder;
        $this->locationHelper = $locationHelper;
        $this->priceHelper = $priceHelper;
    }

    /**
     * @param string|int $orderId
     * @return string
     */
    public function getOrderUrl($orderId): string
    {
        return $this->urlBuilder->getUrl('sales/order/view', [
            'order_id' => $orderId,
        ]);
    }

    /**
     * @param float $price
     * @return string
     */
    public function formatPrice($price): string
    {
        if (!$price) {
            return '';
        }

        return $this->priceHelper->currency($price, true, false);
    }

    /**
     * @param $mysqlTime
     * @return string
     */
    public function formatOrderTime($mysqlTime): string
    {
        if (!$mysqlTime) {
            return '';
        }

        $format = 'M d, h:i A';
        return $this->getTimezone()->date($mysqlTime)->format($format);
    }

    /**
     * @return IpLocationHelper
     */
    public function getLocationHelper(): IpLocationHelper
    {
        return $this->locationHelper;
    }
}
