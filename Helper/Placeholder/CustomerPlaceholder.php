<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Backend\Model\UrlInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class CustomerPlaceholder implements PlaceholderInterface
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
     * @return string
     */
    public function getSearchString(): string
    {
        return 'customer';
    }

    /**
     * @param DataObject $context
     * @return string|null
     */
    public function getReplaceString($context)
    {
        $customerName = $context->getData('customer');
        if (!$customerName) {
            return null;
        }

        $customerId = $context->getData('customer-id');
        if (!$customerId) {
            return $customerName;
        }

        $customer = $this->findCustomerById($customerId);
        if (!$customer) {
            return $customerName;
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
    protected function findCustomerById($id)
    {
        try {
            return $this->customerRepository->getById($id);
        } catch (LocalizedException $e) {
            return null;
        }
    }
}
