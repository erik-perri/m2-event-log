<?php

namespace Ryvon\EventLog\Plugin;

use Magento\Authorization\Model\Role;
use Magento\Framework\Event\ManagerInterface;

/**
 * Plugin to monitor the saving and deletion of admin roles.
 */
class AdminRolePlugin
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        ManagerInterface $eventManager
    ) {
        $this->eventManager = $eventManager;
    }

    /**
     * Wrap the save function to add an event log on save and create.
     *
     * @param Role $subject
     * @param callable $proceed
     * @return mixed
     */
    public function aroundSave(
        /** @noinspection PhpUnusedParameterInspection */ Role $subject,
        callable $proceed
    ) {
        // We proceed with the save to obtain the ID in case this is a new attribute.
        $return = $proceed();

        $this->eventManager->dispatch('event_log_info', [
            'group' => 'admin',
            'message' => 'Admin role {admin-role} {action}.',
            'context' => [
                'admin-role' => $subject->getData('name'),
                'admin-role-id' => (string)$subject->getId(),
                'action' => $subject->isObjectNew() ? 'created' : 'modified',
            ],
        ]);

        return $return;
    }

    /**
     * Add an event log on delete.
     *
     * @param Role $subject
     * @return array
     */
    public function beforeDelete(
        /** @noinspection PhpUnusedParameterInspection */ Role $subject
    ): array {
        $this->eventManager->dispatch('event_log_info', [
            'group' => 'admin',
            'message' => 'Admin role {admin-role} {action}.',
            'context' => [
                'admin-role' => $subject->getData('role_name'),
                'admin-role-id' => (string)$subject->getId(),
                'action' => 'deleted',
            ],
        ]);

        return [];
    }
}