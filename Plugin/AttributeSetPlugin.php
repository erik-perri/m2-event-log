<?php

namespace Ryvon\EventLog\Plugin;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Plugin to monitor the saving and deletion of attribute sets.
 */
class AttributeSetPlugin
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
     * @param Set $subject
     * @param callable $proceed
     * @param AbstractModel $object
     * @return mixed
     */
    public function aroundSave(
        /** @noinspection PhpUnusedParameterInspection */ Set $subject,
        callable $proceed,
        AbstractModel $object
    ) {
        // When an attribute set is created save ends up being called twice, the first time without an ID. We check
        // for ID and ignore the first save rather than proceeding and obtaining the new ID to prevent duplicate
        // entries in the log.
        $id = $object->getData('attribute_set_id');
        if ($id && $this->isEditingAttributeSet()) {
            $this->eventManager->dispatch('event_log_info', [
                'group' => 'admin',
                'message' => 'Attribute Set {attribute-set} {action}.',
                'context' => [
                    'attribute-set' => [
                        'handler' => 'attribute-set',
                        'text' => $object->getData('attribute_set_name'),
                        'id' => $id,
                    ],
                    'action' => $object->isObjectNew() ? 'created' : 'modified',
                ],
            ]);
        }

        return $proceed($object);
    }

    /**
     * Add an event log on delete.
     *
     * @param Set $subject
     * @param AbstractModel $object
     * @return array
     */
    public function beforeDelete(
        /** @noinspection PhpUnusedParameterInspection */ Set $subject,
        AbstractModel $object
    ): array {
        if ($this->isEditingAttributeSet()) {
            $this->eventManager->dispatch('event_log_info', [
                'group' => 'admin',
                'message' => 'Attribute Set {attribute-set} {action}.',
                'context' => [
                    'attribute-set' => [
                        'handler' => 'attribute-set',
                        'text' => $object->getData('attribute_set_name'),
                        'id' => $object->getData('attribute_set_id'),
                    ],
                    'action' => 'deleted',
                ],
            ]);
        }

        return [$object];
    }

    /**
     * Checks whether the current request is a post request to the attribute set edit page.
     *
     * @return bool
     */
    private function isEditingAttributeSet(): bool
    {
        if (!$this->request instanceof Http) {
            return false;
        }

        if (!$this->request->isPost() || !in_array($this->request->getFullActionName(), [
                'catalog_product_set_save',
                'catalog_product_set_delete',
            ])) {
            return false;
        }

        return true;
    }
}
