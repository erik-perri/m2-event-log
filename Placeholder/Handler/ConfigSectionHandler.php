<?php

namespace Ryvon\EventLog\Placeholder\Handler;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;

class ConfigSectionHandler implements HandlerInterface
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
        $section = $context->getData('text');
        if (!$section) {
            return null;
        }

        return $this->buildLinkTag([
            'text' => $section,
            'title' => 'Edit this configuration section in the admin',
            'href' => $this->urlBuilder->getUrl('adminhtml/system_config/edit', [
                'section' => $section,
            ]),
            'target' => '_blank',
        ]);
    }
}
