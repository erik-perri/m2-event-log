<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;

class DesignConfigScopePlaceholder implements PlaceholderInterface
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
        $scope = $context->getData('design-config-scope');
        if (!$scope) {
            return null;
        }

        $params = [
            'scope' => $scope,
        ];

        $scopeId = $context->getData('design-config-scope-id');
        if ($scopeId) {
            $params['scope_id'] = $scopeId;
        }

        return $this->buildLinkTag([
            'text' => $scope,
            'title' => 'Edit this configuration scope in the admin',
            'href' => $this->urlBuilder->getUrl('theme/design_config/edit', $params),
            'target' => '_blank',
        ]);
    }
}
