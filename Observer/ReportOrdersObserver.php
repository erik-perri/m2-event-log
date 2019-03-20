<?php

namespace Ryvon\EventLog\Observer;

use Ryvon\EventLog\Helper\OrderReporter;
use Ryvon\EventLog\Model\Digest;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

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
    )
    {
        $this->logger = $logger;
        $this->orderReporter = $orderReporter;
    }

    /**
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
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}
