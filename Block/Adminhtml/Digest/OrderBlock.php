<?php

namespace Ryvon\EventLog\Block\Adminhtml\Digest;

use Ryvon\EventLog\Helper\DigestRequestHelper;
use Ryvon\EventLog\Helper\IpLocationHelper;
use Ryvon\EventLog\Helper\PlaceholderReplacer;
use Magento\Backend\Block\Template;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Stdlib\DateTime\Timezone;

/**
 * Block class for the order entry block for both the administrator and email.
 */
class OrderBlock extends EntryBlock
{
    /**
     * @var IpLocationHelper
     */
    private $locationHelper;

    /**
     * @var PricingHelper
     */
    private $priceHelper;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param DigestRequestHelper $digestRequestHelper
     * @param IpLocationHelper $locationHelper
     * @param PlaceholderReplacer $placeholderReplacer
     * @param PricingHelper $priceHelper
     * @param Timezone $timezone
     * @param UrlInterface $urlBuilder
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        DigestRequestHelper $digestRequestHelper,
        IpLocationHelper $locationHelper,
        PlaceholderReplacer $placeholderReplacer,
        PricingHelper $priceHelper,
        Timezone $timezone,
        UrlInterface $urlBuilder,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($digestRequestHelper, $placeholderReplacer, $timezone, $context, $data);

        $this->locationHelper = $locationHelper;
        $this->priceHelper = $priceHelper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Generates an order edit URL for the specified order.
     *
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
     * Formats the specified price using the default Magento price formatter.
     *
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
     * Formats the specified time for the order table.
     *
     * @param string|\DateTime $mysqlTime
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
     * Helper function to retrieve the IP location helper.
     *
     * @return IpLocationHelper
     */
    public function getLocationHelper(): IpLocationHelper
    {
        return $this->locationHelper;
    }
}
