<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;

class AttributeSetPlaceholder implements PlaceholderInterface
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
    public function getSearchString()
    {
        return 'attribute-set';
    }

    /**
     * @param DataObject $context
     * @return string|null
     */
    public function getReplaceString($context)
    {
        $attributeSet = $context->getData('attribute-set');
        if (!$attributeSet) {
            return null;
        }

        $attributeSetId = $context->getData('attribute-set-id');
        if (!$attributeSetId) {
            return $attributeSet;
        }

        return $this->buildLinkTag([
            'text' => $attributeSet,
            'title' => 'Edit this attribute set in the admin',
            'href' => $this->urlBuilder->getUrl('catalog/product_set/edit', [
                'id' => $attributeSetId,
            ]),
            'target' => '_blank',
        ]);
    }
}
