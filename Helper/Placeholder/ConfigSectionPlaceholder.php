<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;

class ConfigSectionPlaceholder implements PlaceholderInterface
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
        return 'config-section';
    }

    /**
     * @param DataObject $context
     * @return string|null
     */
    public function getReplaceString($context)
    {
        $section = $context->getData('config-section');
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
