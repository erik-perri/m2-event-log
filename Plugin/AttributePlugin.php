<?php

namespace Ryvon\EventLog\Plugin;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\ManagerInterface;
use Ryvon\EventLog\Helper\Group\AdminGroup;

class AttributePlugin
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @param ManagerInterface $eventManager
     * @param Session $authSession
     */
    public function __construct(ManagerInterface $eventManager, Session $authSession)
    {
        $this->eventManager = $eventManager;
        $this->authSession = $authSession;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $subject
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array
     */
    public function beforeSave(
        /** @noinspection PhpUnusedParameterInspection */
        \Magento\Catalog\Model\ResourceModel\Attribute $subject,
        \Magento\Framework\Model\AbstractModel $object
    ): array
    {
        if ($this->authSession->getUser()) {
            $this->eventManager->dispatch('event_log_info', [
                'group' => AdminGroup::GROUP_ID,
                'message' => 'Attribute {attribute} {action}.',
                'context' => [
                    'attribute' => $object->getData('attribute_code'),
                    'attribute-id' => $object->getData('attribute_id'),
                    'action' => $object->isObjectNew() ? 'created' : 'modified',
                ],
            ]);
        }

        return [$object];
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Attribute $subject
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array
     */
    public function beforeDelete(
        /** @noinspection PhpUnusedParameterInspection */
        \Magento\Catalog\Model\ResourceModel\Attribute $subject,
        \Magento\Framework\Model\AbstractModel $object
    ): array
    {
        if ($this->authSession->getUser()) {
            $this->eventManager->dispatch('event_log_info', [
                'group' => AdminGroup::GROUP_ID,
                'message' => 'Attribute {attribute} {action}.',
                'context' => [
                    'attribute' => $object->getData('attribute_code'),
                    'action' => 'deleted',
                ],
            ]);
        }

        return [$object];
    }
}
