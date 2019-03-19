<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;

class AttributePlaceholder implements PlaceholderInterface
{
    use LinkPlaceholderTrait;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     */
    public function __construct(UrlInterface $urlBuilder)
    {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return string
     */
    public function getSearchString(): string
    {
        return 'attribute';
    }

    /**
     * @param DataObject $context
     * @return string|null
     */
    public function getReplaceString($context)
    {
        $attributeCode = $context->getData('attribute');
        if (!$attributeCode) {
            return null;
        }

        $attributeId = $context->getData('attribute-id');
        if (!$attributeId) {
            return $attributeCode;
        }

        return $this->buildLinkTag([
            'text' => $attributeCode,
            'title' => 'Edit this attribute in the admin',
            'href' => $this->urlBuilder->getUrl('catalog/product_attribute/edit', [
                'attribute_id' => $attributeId,
            ]),
            'target' => '_blank',
        ]);
    }
}
