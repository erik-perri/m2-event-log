<?php

namespace Ryvon\EventLog\Placeholder\Handler;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;

class DesignConfigHandler implements HandlerInterface
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
        $scope = $context->getData('text');
        if (!$scope) {
            return null;
        }

        $params = [
            'scope' => $scope,
        ];

        $scopeId = $context->getData('id');
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
