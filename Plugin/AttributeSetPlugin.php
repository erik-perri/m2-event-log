<?php

namespace Ryvon\EventLog\Plugin;

use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Event\ManagerInterface;
use Ryvon\EventLog\Helper\Group\AdminGroup;

class AttributeSetPlugin
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
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set $subject
     * @param callable $proceed
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return mixed
     */
    public function aroundSave(
        /** @noinspection PhpUnusedParameterInspection */
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set $subject,
        callable $proceed,
        \Magento\Framework\Model\AbstractModel $object
    )
    {
        if ($this->authSession->getUser()) {
            $this->eventManager->dispatch('event_log_info', [
                'group' => AdminGroup::GROUP_ID,
                'message' => 'Attribute Set {attribute-set} {action}.',
                'context' => [
                    'attribute-set' => $object->getData('attribute_set_name'),
                    'attribute-set-id' => $object->getData('attribute_set_id'),
                    'action' => $object->isObjectNew() ? 'created' : 'modified',
                ],
            ]);
        }

        return $proceed($object);
    }

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set $subject
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return array
     */
    public function beforeDelete(
        /** @noinspection PhpUnusedParameterInspection */
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set $subject,
        \Magento\Framework\Model\AbstractModel $object
    )
    {
        if ($this->authSession->getUser()) {
            $this->eventManager->dispatch('event_log_info', [
                'group' => AdminGroup::GROUP_ID,
                'message' => 'Attribute Set {attribute-set} {action}.',
                'context' => [
                    'attribute-set' => $object->getData('attribute_set_name'),
                    'action' => 'deleted',
                ],
            ]);
        }

        return [$object];
    }
}
