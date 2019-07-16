<?php

namespace Ryvon\EventLog\Placeholder\Handler;

use Magento\Authorization\Model\ResourceModel\Role as RoleResourceModel;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;

/**
 * Placeholder to replace {admin-role} with a link to edit role form.
 */
class RoleHandler implements HandlerInterface
{
    use LinkPlaceholderTrait;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var RoleResourceModel
     */
    private $roleResourceModel;

    /**
     * @var RoleFactory
     */
    private $roleFactory;

    /**
     * @param UrlInterface $urlBuilder
     * @param RoleResourceModel $roleResourceModel
     * @param RoleFactory $roleFactory
     */
    public function __construct(
        UrlInterface $urlBuilder,
        RoleResourceModel $roleResourceModel,
        RoleFactory $roleFactory
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->roleResourceModel = $roleResourceModel;
        $this->roleFactory = $roleFactory;
    }

    /**
     * @inheritDoc
     */
    public function handle(DataObject $context)
    {
        $roleId = $context->getData('id');
        $roleName = $context->getData('text');
        if (!$roleId || !$roleName) {
            return null;
        }

        $role = $this->findRoleById($roleId);
        if (!$role) {
            return null;
        }

        return $this->buildLinkTag([
            'text' => $roleName,
            'title' => 'Edit this role in the admin',
            'href' => $this->urlBuilder->getUrl('adminhtml/user_role/editrole', [
                'rid' => $roleId,
            ]),
            'target' => '_blank',
        ]);
    }

    /**
     * Loads the admin role with the specified ID.
     *
     * @param int $id
     * @return Role|null
     */
    private function findRoleById($id)
    {
        $role = $this->roleFactory->create();
        $this->roleResourceModel->load($role, $id);

        return $role->getId() ? $role : null;
    }
}
