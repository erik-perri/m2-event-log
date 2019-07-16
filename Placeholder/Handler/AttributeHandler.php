<?php

namespace Ryvon\EventLog\Placeholder\Handler;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;

/**
 * Placeholder to replace {attribute} with a link to edit the related item.
 */
class AttributeHandler implements HandlerInterface
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
     * @inheritDoc
     */
    public function handle(DataObject $context)
    {
        $attributeId = $context->getData('id');
        $attributeCode = $context->getData('text');
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
