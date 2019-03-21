<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;
use Magento\User\Model\ResourceModel\User;
use Magento\User\Model\UserFactory;

class UserNamePlaceholder implements PlaceholderInterface
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
     * @return string
     */
    public function getSearchString(): string
    {
        return 'user-name';
    }

    /**
     * @param DataObject $context
     * @return string|null
     */
    public function getReplaceString($context)
    {
        $userId = $context->getData('user-id');
        $userName = $context->getData('user-name');

        if (!$userId && !$userName) {
            return null;
        }
        if (!$userId) {
            return $userName;
        }

        $user = $this->findUserById($userId);

        return $user ? $this->buildLinkTag([
            'text' => $user->getUserName(),
            'title' => sprintf('Edit %s (%s) in the admin', $user->getUserName(), $user->getEmail()),
            'href' => $this->urlBuilder->getUrl('adminhtml/user/edit', [
                'user_id' => $user->getId(),
            ]),
            'target' => '_blank',
        ]) : $userName;
    }

    /**
     * @param $id
     * @return \Magento\User\Model\User|null
     */
    private function findUserById($id)
    {
        $user = $this->userFactory->create();
        $this->userResourceModel->load($user, $id);

        return $user->getId() ? $user : null;
    }
}
