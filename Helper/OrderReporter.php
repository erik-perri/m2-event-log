<?php

namespace Ryvon\EventLog\Helper;

use Ryvon\EventLog\Model\Digest;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

class OrderReporter
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortBuilder;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortBuilder
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortBuilder,
        ManagerInterface $eventManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortBuilder = $sortBuilder;
        $this->eventManager = $eventManager;
    }

    /**
     * @param Digest $digest
     */
    public function reportOrdersInDigest(Digest $digest)
    {
        try {
            $now = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return;
        }

        $orders = $this->findOrders(
            $digest->getStartedAt(),
            $digest->getFinishedAt() ?: $now
        );

        foreach ($orders as $order) {
            $this->reportOrder($digest, $order);
        }
    }

    /**
     * @param string $startMysqlTime
     * @param string $endMysqlTime
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection|\Magento\Sales\Api\Data\OrderSearchResultInterface
     */
    private function findOrders($startMysqlTime, $endMysqlTime)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', 'canceled', 'neq')
            ->addFilter('created_at', $startMysqlTime, 'gt')
            ->addFilter('created_at', $endMysqlTime, 'lt')
            ->addSortOrder($this->sortBuilder->setField('entity_id')->setDescendingDirection()->create())
            ->create();

        return $this->orderRepository->getList($searchCriteria);
    }

    /**
     * @param Digest $digest
     * @param Order $order
     */
    public function reportOrder(Digest $digest, Order $order)
    {
        $ips = array_merge([$order->getRemoteIp()], explode(',', $order->getXForwardedFor()));
        $level = 'info';

        switch ($order->getStatus()) {
            case 'pending':
                $level = 'error';
                break;
            case 'processing':
                $level = 'warning';
                break;
        }

        $this->eventManager->dispatch('event_log_' . $level, [
            'digest' => $digest,
            'group' => 'orders',
            'message' => 'Order {order} placed by {bill-to-name} for {price} is {status}.',
            'date' => $order->getCreatedAt(),
            'context' => [
                'order' => $order->getIncrementId(),
                'order-id' => $order->getId(),
                'created-at' => $order->getCreatedAt(),
                'bill-to-name' => $this->getOrderBillToName($order),
                'price' => $order->getGrandTotal(),
                'status' => $order->getStatusLabel(),
                'status-code' => $order->getStatus(),
                'store-view' => $order->getStore() ? $order->getStore()->getFrontendName() : null,
                'payment-method' => $this->getOrderPaymentMethodTitle($order),
                'ips' => array_values(array_unique(array_filter($ips, function ($ip) {
                    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
                })))
            ],
        ]);
    }

    /**
     * @param Order $order
     * @return string|null
     */
    private function getOrderBillToName(Order $order)
    {
        $billTo = $order->getBillingAddress();
        if (!$billTo) {
            return null;
        }

        return $billTo->getFirstname() . ' ' . $billTo->getLastname();
    }

    /**
     * @param Order $order
     * @return string|null
     */
    private function getOrderPaymentMethodTitle(Order $order)
    {
        try {
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $order->getPayment();
            $method = $payment->getMethodInstance();
            return $method->getTitle();
        } catch (LocalizedException $e) {
            return null;
        }
    }

    /**
     * @param Digest $digest
     * @param string $incrementId
     */
    public function reportOrderByIncrementId(Digest $digest, $incrementId)
    {
        $order = $this->findOrderByIncrementId($incrementId);
        if ($order) {
            $this->reportOrder($digest, $order);
        }
    }

    /**
     * @param string $incrementId
     * @return Order|null
     */
    private function findOrderByIncrementId($incrementId)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $incrementId)->create();
        $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
        if (count($orderList)) {
            return reset($orderList);
        }
        return null;
    }
}
