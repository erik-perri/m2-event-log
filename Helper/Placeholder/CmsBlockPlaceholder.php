<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;

class CmsBlockPlaceholder implements PlaceholderInterface
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
     * @param DataObject $context
     * @return string|null
     */
    public function getReplaceString($context)
    {
        $blockId = $context->getData('cms-block-id');
        $blockName = $context->getData('cms-block');
        if (!$blockId || !$blockName) {
            return null;
        }

        return $this->buildLinkTag([
            'text' => $blockName,
            'title' => 'Edit this block in the admin',
            'href' => $this->urlBuilder->getUrl('cms/block/edit', [
                'block_id' => $blockId,
            ]),
            'target' => '_blank',
        ]);
    }
}
