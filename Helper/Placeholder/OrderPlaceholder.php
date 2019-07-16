<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DataObject;
use Magento\Sales\Model\OrderRepository;

class OrderPlaceholder implements PlaceholderInterface
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
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return string
     */
    public function getSearchString(): string
    {
        return 'order';
    }

    /**
     * @param DataObject $context
     * @return string|null
     */
    public function getReplaceString($context)
    {
        $orderId = $context->getData('order');
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
     * @return \Magento\Sales\Api\Data\OrderInterface|null
     */
    private function findOrderById($incrementId)
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $incrementId)->create();
            $orderList = $this->orderRepository->getList($searchCriteria)->getItems();
            if (count($orderList)) {
                return reset($orderList);
            }
        } catch (\Exception $e) {
        }
        return null;
    }
}
