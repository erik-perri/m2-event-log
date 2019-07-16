<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Backend\Model\UrlInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\DataObject;
use Ryvon\EventLog\Helper\ImageLocator;

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
     * @var ImageLocator
     */
    private $imageLocator;

    /**
     * @param UrlInterface $urlBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param ImageLocator $imageLocator
     */
    public function __construct(
        UrlInterface $urlBuilder,
        ProductRepositoryInterface $productRepository,
        ImageLocator $imageLocator
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->productRepository = $productRepository;
        $this->imageLocator = $imageLocator;
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
            return null;
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
                    'html' => $this->imageLocator->getIconSvg('store') ?: '[Frontend]',
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
