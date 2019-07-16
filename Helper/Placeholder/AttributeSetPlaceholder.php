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
     * @param DataObject $context
     * @return string|null
     */
    public function getReplaceString($context)
    {
        $attributeSetId = $context->getData('attribute-set-id');
        $attributeSet = $context->getData('attribute-set');
        if (!$attributeSetId || !$attributeSet) {
            return null;
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
