<?php

namespace Ryvon\EventLog\Placeholder\Handler;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;

/**
 * Placeholder to replace {attribute-set} with a link to edit the related item.
 */
class AttributeSetHandler implements HandlerInterface
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
        $attributeSetId = $context->getData('id');
        $attributeSet = $context->getData('text');
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
