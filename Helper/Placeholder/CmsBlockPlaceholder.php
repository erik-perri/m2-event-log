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
     * @return string
     */
    public function getSearchString()
    {
        return 'cms-page';
    }

    /**
     * @param DataObject $context
     * @return string
     */
    public function getReplaceString($context)
    {
        $blockName = $context->getData('cms-block');
        if (!$blockName) {
            return false;
        }

        $blockId = $context->getData('cms-block-id');
        if (!$blockId) {
            return $blockName;
        }

        $return = $this->buildLinkTag([
            'text' => $blockName,
            'title' => 'Edit this block in the admin',
            'href' => $this->urlBuilder->getUrl('cms/block/edit', [
                'block_id' => $blockId,
            ]),
            'target' => '_blank',
        ]);

        return $return;
    }
}
