<?php

namespace Ryvon\EventLog\Plugin;

use Magento\Framework\Event\ManagerInterface;
use Magento\User\Model\ResourceModel\User as UserResourceModel;
use Magento\User\Model\User;
use Magento\User\Model\UserFactory;

/**
 * Plugin to monitor the saving and deletion of admin users.
 */
class AdminUserPlugin
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var UserResourceModel
     */
    private $userResourceModel;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @param ManagerInterface $eventManager
     * @param UserResourceModel $userResourceModel
     * @param UserFactory $userFactory
     */
    public function __construct(
        ManagerInterface $eventManager,
        UserResourceModel $userResourceModel,
        UserFactory $userFactory
    ) {
        $this->eventManager = $eventManager;
        $this->userResourceModel = $userResourceModel;
        $this->userFactory = $userFactory;
    }

    /**
     * Wrap the save function to add an event log on save and create.
     *
     * @param User $subject
     * @param callable $proceed
     * @return mixed
     */
    public function aroundSave(
        /** @noinspection PhpUnusedParameterInspection */ User $subject,
        callable $proceed
    ) {
        // We proceed with the save to obtain the ID in case this is a new attribute.
        $return = $proceed();

        $this->eventManager->dispatch('event_log_info', [
            'group' => 'admin',
            'message' => 'Admin user {admin-user} {action}.',
            'context' => [
                'admin-user' => $subject->getData('username'),
                'admin-user-id' => (string)$subject->getId(),
                'action' => $subject->isObjectNew() ? 'created' : 'modified',
            ],
        ]);

        return $return;
    }

    /**
     * Add an event log on delete.
     *
     * @param User $subject
     * @return array
     */
    public function beforeDelete(
        /** @noinspection PhpUnusedParameterInspection */ User $subject
    ): array {
        $user = $this->userFactory->create();
        $this->userResourceModel->load($user, $subject->getId());

        if ($user->getId()) {
            $this->eventManager->dispatch('event_log_info', [
                'group' => 'admin',
                'message' => 'Admin user {admin-user} {action}.',
                'context' => [
                    'admin-user' => $user->getData('username'),
                    'admin-user-id' => (string)$user->getId(),
                    'action' => 'deleted',
                ],
            ]);
        }

        return [];
    }
}
