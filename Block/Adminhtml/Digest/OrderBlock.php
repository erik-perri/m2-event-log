<?php

namespace Ryvon\EventLog\Block\Adminhtml\Digest;

use DateTime;
use Exception;
use Magento\Backend\Block\Template;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Ryvon\EventLog\Helper\DigestRequestHelper;
use Ryvon\EventLog\Placeholder\PlaceholderProcessor;

/**
 * Block class for the order entry block for both the administrator and email.
 */
class OrderBlock extends EntryBlock
{
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
     * @param PlaceholderProcessor $placeholderProcessor
     * @param PricingHelper $priceHelper
     * @param Timezone $timezone
     * @param UrlInterface $urlBuilder
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        DigestRequestHelper $digestRequestHelper,
        PlaceholderProcessor $placeholderProcessor,
        PricingHelper $priceHelper,
        Timezone $timezone,
        UrlInterface $urlBuilder,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($digestRequestHelper, $placeholderProcessor, $timezone, $context, $data);

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
     * @param string|DateTime $mysqlTime
     * @return string
     */
    public function formatOrderTime($mysqlTime): string
    {
        if (!$mysqlTime) {
            return '';
        }

        $format = 'M d, h:i A';
        try {
            return $this->getTimezone()->date(new \DateTime($mysqlTime))->format($format);
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * Renders the specified IP address using the user-ip placeholder.
     *
     * @param string|null $ipAddress
     * @return string
     */
    public function formatIpAddress($ipAddress): string
    {
        if (!$ipAddress) {
            return '';
        }

        return $this->replacePlaceholders('{ip-address}', [
            'ip-address' => $ipAddress,
        ]);
    }
}
