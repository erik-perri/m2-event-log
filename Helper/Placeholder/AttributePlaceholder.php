<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;

/**
 * Placeholder to replace {attribute} with a link to edit the related item.
 */
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
     * @inheritdoc
     *
     * @param DataObject $context
     * @return string|null
     */
    public function getReplaceString($context)
    {
        $attributeId = $context->getData('attribute-id');
        $attributeCode = $context->getData('attribute');
        if (!$attributeId || !$attributeCode) {
            return null;
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
