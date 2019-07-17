<?php

namespace Ryvon\EventLog\Placeholder\Handler;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;

class CmsBlockHandler implements HandlerInterface
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
        $blockId = $context->getData('id');
        $blockName = $context->getData('text');
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
