<?php

namespace Ryvon\EventLog\Plugin;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
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
     * @var RequestInterface
     */
    private $request;

    /**
     * @param ManagerInterface $eventManager
     * @param UserResourceModel $userResourceModel
     * @param UserFactory $userFactory
     * @param RequestInterface $request
     */
    public function __construct(
        ManagerInterface $eventManager,
        UserResourceModel $userResourceModel,
        UserFactory $userFactory,
        RequestInterface $request
    ) {
        $this->eventManager = $eventManager;
        $this->userResourceModel = $userResourceModel;
        $this->userFactory = $userFactory;
        $this->request = $request;
    }

    /**
     * Wrap the save function to add an event log on save and create.
     *
     * @param User $subject
     * @param callable $proceed
     * @return mixed
     */
    public function aroundSave(User $subject, callable $proceed)
    {
        // We proceed with the save to obtain the ID in case this is a new user.
        $return = $proceed();

        if ($this->isEditingUser()) {
            $this->eventManager->dispatch('event_log_info', [
                'group' => 'admin',
                'message' => 'Admin user {user} {action}.',
                'context' => [
                    'user' => [
                        'text' => $subject->getUserName(),
                        'id' => $subject->getId(),
                    ],
                    'action' => $subject->isObjectNew() ? 'created' : 'modified',
                ],
            ]);
        }

        return $return;
    }

    /**
     * Add an event log on delete.
     *
     * @param User $subject
     * @return array
     */
    public function beforeDelete(User $subject): array
    {
        // We load the user since during testing the subject only contained the ID, not the username.
        $user = $this->userFactory->create();
        $this->userResourceModel->load($user, $subject->getId());

        if ($user->getId()) {
            $this->eventManager->dispatch('event_log_info', [
                'group' => 'admin',
                'message' => 'Admin user {user} {action}.',
                'context' => [
                    'user' => [
                        'text' => $user->getUserName(),
                        'id' => $user->getId(),
                    ],
                    'action' => 'deleted',
                ],
            ]);
        }

        return [];
    }

    /**
     * Checks whether the current request is a post request to the user edit page.
     *
     * This is needed due to the role form modifying the user on save.
     *
     * @return bool
     */
    private function isEditingUser(): bool
    {
        if (!$this->request instanceof Http) {
            return false;
        }

        if (!$this->request->isPost() || !in_array($this->request->getFullActionName(), [
                // Editing their own account
                'adminhtml_system_account_save',
                'adminhtml_system_account_delete',
                // Editing another user's account
                'adminhtml_user_save',
                'adminhtml_user_delete',
            ])) {
            return false;
        }

        return true;
    }
}
