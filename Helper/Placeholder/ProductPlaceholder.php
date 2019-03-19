<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Backend\Model\UrlInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Ryvon\EventLog\Helper\SvgHelper;

class ProductPlaceholder implements PlaceholderInterface
{
    use LinkPlaceholderTrait;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SvgHelper
     */
    private $svgHelper;

    /**
     * @param UrlInterface $urlBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param SvgHelper $svgHelper
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        SvgHelper $svgHelper
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->svgHelper = $svgHelper;
    }

    /**
     * @return string
     */
    public function getSearchString(): string
    {
        return 'product';
    }

    /**
     * @param DataObject $context
     * @return string|null
     */
    public function getReplaceString($context)
    {
        $productSku = $context->getData('product');
        if (!$productSku) {
            return null;
        }

        $product = $this->findProductBySku($productSku);
        if (!$product || !($product instanceof Product)) {
            return $productSku;
        }

        $return = $this->buildLinkTag([
            'text' => $product->getName(),
            'title' => 'Edit this product in the admin',
            'href' => $this->urlBuilder->getUrl('catalog/product/edit', [
                'id' => $product->getId(),
            ]),
            'target' => '_blank',
        ]);

        $frontendUrl = $this->getStoreUrl() . $product->getUrlKey();
        if ($frontendUrl) {
            $return .= $this->buildLinkTag([
                'html' => $this->svgHelper->getStoreSvg(),
                'title' => 'View this product on the frontend',
                'href' => $frontendUrl,
                'target' => '_blank',
                'class' => 'icon',
            ]);
        }

        return $return;
    }

    /**
     * @param string $productSku
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    protected function findProductBySku($productSku)
    {
        try {
            return $this->productRepository->get($productSku);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @return string|null
     */
    protected function getStoreUrl()
    {
        try {
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->storeManager->getStore();
            return $store->getBaseUrl();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
