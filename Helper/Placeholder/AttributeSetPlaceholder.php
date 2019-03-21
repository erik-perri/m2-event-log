<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;

/**
 * Placeholder to replace {attribute-set} with a link to edit the related item.
 */
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
     * @inheritdoc
     *
     * @return string
     */
    public function getSearchString(): string
    {
        return 'attribute-set';
    }

    /**
     * @inheritdoc
     *
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
