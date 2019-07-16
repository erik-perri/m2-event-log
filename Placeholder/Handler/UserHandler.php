<?php

namespace Ryvon\EventLog\Placeholder\Handler;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;
use Magento\User\Model\ResourceModel\User;
use Magento\User\Model\UserFactory;

/**
 * Placeholder to replace {user-name} with a link to edit user form.
 */
class UserHandler implements HandlerInterface
{
    use LinkPlaceholderTrait;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * @var User
     */
    private $userResourceModel;

    /**
     * @param UrlInterface $urlBuilder
     * @param User $userResourceModel
     * @param UserFactory $userFactory
     */
    public function __construct(
        UrlInterface $urlBuilder,
        User $userResourceModel,
        UserFactory $userFactory
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->userFactory = $userFactory;
        $this->userResourceModel = $userResourceModel;
    }

    /**
     * @inheritDoc
     */
    public function handle(DataObject $context)
    {
        $userId = $context->getData('id');
        $userName = $context->getData('text');
        if (!$userId || !$userName) {
            return null;
        }

        $user = $this->findUserById($userId);
        if (!$user) {
            return null;
        }

        return $this->buildLinkTag([
            'text' => $user->getUserName(),
            'title' => sprintf('Edit %s (%s) in the admin', $user->getUserName(), $user->getEmail()),
            'href' => $this->urlBuilder->getUrl('adminhtml/user/edit', [
                'user_id' => $user->getId(),
            ]),
            'target' => '_blank',
        ]);
    }

    /**
     * Loads the admin user with the specified ID.
     *
     * @param int $id
     * @return \Magento\User\Model\User|null
     */
    private function findUserById($id)
    {
        $user = $this->userFactory->create();
        $this->userResourceModel->load($user, $id);

        return $user->getId() ? $user : null;
    }
}
