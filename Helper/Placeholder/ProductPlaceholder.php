<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Ryvon\EventLog\Helper\ImageFinder;
use Magento\Backend\Model\UrlInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;

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
     * @var ImageFinder
     */
    private $imageFinder;

    /**
     * @param UrlInterface $urlBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param ImageFinder $imageFinder
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ProductRepositoryInterface $productRepository,
        ImageFinder $imageFinder
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->productRepository = $productRepository;
        $this->imageFinder = $imageFinder;
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

        if ((int)$product->getStatus() === Product\Attribute\Source\Status::STATUS_ENABLED) {
            $frontendUrl = $product->getUrlModel()->getUrl($product);
            if ($frontendUrl) {
                $return .= $this->buildLinkTag([
                    'html' => $this->imageFinder->getSvgContents('store.svg'),
                    'title' => 'View this product on the frontend',
                    'href' => $frontendUrl,
                    'target' => '_blank',
                    'class' => 'icon',
                ]);
            }
        }

        return $return;
    }

    /**
     * @param string $productSku
     * @return \Magento\Catalog\Api\Data\ProductInterface|null
     */
    private function findProductBySku($productSku)
    {
        try {
            return $this->productRepository->get($productSku);
        } catch (\Exception $e) {
            return null;
        }
    }
}
