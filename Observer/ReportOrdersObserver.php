<?php

namespace Ryvon\EventLog\Observer;

use Exception;
use Ryvon\EventLog\Helper\OrderReporter;
use Ryvon\EventLog\Model\Digest;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

/**
 * Event observer to add the orders from the digest period to the digest before sending.
 */
class ReportOrdersObserver implements ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderReporter
     */
    private $orderReporter;

    /**
     * @param LoggerInterface $logger
     * @param OrderReporter $orderReporter
     */
    public function __construct(
        LoggerInterface $logger,
        OrderReporter $orderReporter
    ) {
        $this->logger = $logger;
        $this->orderReporter = $orderReporter;
    }

    /**
     * Adds the orders made in the digest period to the digest.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $digest = $observer->getData('digest');
            if (!$digest || !$digest instanceof Digest) {
                return;
            }

            $this->orderReporter->reportOrdersInDigest($digest);
        } catch (Exception $e) {
            $this->logger->critical($e);
        }
    }
}
