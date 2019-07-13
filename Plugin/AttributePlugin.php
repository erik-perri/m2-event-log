<?php

namespace Ryvon\EventLog\Plugin;

use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Plugin to monitor the saving and deletion of attributes.
 */
class AttributePlugin
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param ManagerInterface $eventManager
     * @param RequestInterface $request
     */
    public function __construct(ManagerInterface $eventManager, RequestInterface $request)
    {
        $this->eventManager = $eventManager;
        $this->request = $request;
    }

    /**
     * Wrap the save function to add an event log on save and create.
     *
     * @param Attribute $subject
     * @param callable $proceed
     * @param AbstractModel $object
     * @return mixed
     */
    public function aroundSave(
        /** @noinspection PhpUnusedParameterInspection */ Attribute $subject,
        callable $proceed,
        AbstractModel $object
    ) {
        if ($this->isEditingAttribute()) {
            // We proceed with the save to obtain the ID in case this is a new attribute.
            $return = $proceed($object);

            $this->eventManager->dispatch('event_log_info', [
                'group' => 'admin',
                'message' => 'Attribute {attribute} {action}.',
                'context' => [
                    'attribute' => $object->getData('attribute_code'),
                    'attribute-id' => $object->getData('attribute_id'),
                    'action' => $object->isObjectNew() ? 'created' : 'modified',
                ],
            ]);

            return $return;
        }

        return $proceed($object);
    }

    /**
     * Add an event log on delete.
     *
     * @param Attribute $subject
     * @param AbstractModel $object
     * @return array
     */
    public function beforeDelete(
        /** @noinspection PhpUnusedParameterInspection */ Attribute $subject,
        AbstractModel $object
    ): array {
        if ($this->isEditingAttribute()) {
            $this->eventManager->dispatch('event_log_info', [
                'group' => 'admin',
                'message' => 'Attribute {attribute} {action}.',
                'context' => [
                    'attribute' => $object->getData('attribute_code'),
                    'action' => 'deleted',
                ],
            ]);
        }

        return [$object];
    }

    /**
     * Checks whether the current request is a post request to the attribute edit page.
     *
     * This is needed due to the configuration modifying the attributes on save (catalog settings).
     *
     * @return bool
     */
    private function isEditingAttribute(): bool
    {
        if (!$this->request instanceof Http) {
            return false;
        }

        if (!$this->request->isPost() || !in_array($this->request->getFullActionName(), [
                'catalog_product_attribute_save',
                'catalog_product_attribute_delete',
            ])) {
            return false;
        }

        return true;
    }
}
