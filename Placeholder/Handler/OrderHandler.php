<?php

namespace Ryvon\EventLog\Placeholder\Handler;

use Exception;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;

class OrderHandler implements HandlerInterface
{
    use LinkPlaceholderTrait;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param UrlInterface $urlBuilder
     * @param OrderRepository $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder,
        OrderRepository $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function handle(DataObject $context)
    {
        $orderId = $context->getData('text');
        if (!$orderId) {
            return null;
        }

        $order = $this->findOrderById($orderId);
        if (!$order) {
            return null;
        }

        return $this->buildLinkTag([
            'text' => $orderId,
            'title' => 'View this order in the admin',
            'href' => $this->urlBuilder->getUrl('sales/order/view', [
                'order_id' => $order->getEntityId(),
            ]),
            'target' => '_blank',
        ]);
    }

    /**
     * @param string $incrementId
     * @return OrderInterface|null
     */
    private function findOrderById($incrementId)
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $incrementId)->create();
            $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
            if (count($orderList)) {
                return reset($orderList);
            }
        } catch (Exception $e) {
        }
        return null;
    }
}
