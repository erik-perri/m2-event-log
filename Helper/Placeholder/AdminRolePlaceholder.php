<?php

namespace Ryvon\EventLog\Helper\Placeholder;

use Magento\Authorization\Model\ResourceModel\Role as RoleResourceModel;
use Magento\Authorization\Model\Role;
use Magento\Authorization\Model\RoleFactory;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\DataObject;

/**
 * Placeholder to replace {admin-role} with a link to edit role form.
 */
class AdminRolePlaceholder implements PlaceholderInterface
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
     * @inheritdoc
     *
     * @return string
     */
    public function getSearchString(): string
    {
        return 'admin-role';
    }

    /**
     * @inheritdoc
     *
     * @param DataObject $context
     * @return string|null
     */
    public function getReplaceString($context)
    {
        $roleName = $context->getData('admin-role');
        if (!$roleName) {
            return null;
        }

        $roleId = $context->getData('admin-role-id');
        if (!$roleId) {
            return $roleName;
        }

        $role = $this->findRoleById($roleId);

        return $role ? $this->buildLinkTag([
            'text' => $roleName,
            'title' => 'Edit this role in the admin',
            'href' => $this->urlBuilder->getUrl('adminhtml/user_role/editrole', [
                'rid' => $roleId,
            ]),
            'target' => '_blank',
        ]) : $roleName;
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
