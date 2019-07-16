<?php

namespace Ryvon\EventLog\Placeholder\Handler;

use Magento\Backend\Model\UrlInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class CustomerHandler implements HandlerInterface
{
    use LinkPlaceholderTrait;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @param UrlInterface $urlBuilder
     * @param CustomerRepository $customerRepository
     */
    public function __construct(
        UrlInterface $urlBuilder,
        CustomerRepository $customerRepository
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritDoc
     */
    public function handle(DataObject $context)
    {
        $customerId = $context->getData('id');
        $customerName = $context->getData('text');
        if (!$customerId || !$customerName) {
            return null;
        }

        $customer = $this->findCustomerById($customerId);
        if (!$customer) {
            return null;
        }

        $return = $this->buildLinkTag([
            'text' => $customerName,
            'title' => 'Edit this customer in the admin',
            'href' => $this->urlBuilder->getUrl('customer/index/edit', [
                'id' => $customer->getId(),
            ]),
            'target' => '_blank',
        ]);

        return $return;
    }

    /**
     * @param $id
     * @return CustomerInterface|null
     */
    private function findCustomerById($id)
    {
        try {
            return $this->customerRepository->getById($id);
        } catch (LocalizedException $e) {
            return null;
        }
    }
}
