<?php

namespace Ryvon\EventLog\Placeholder\Handler;

use Magento\Backend\Model\UrlInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\ResourceModel\GroupRepository;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class CustomerGroupHandler implements HandlerInterface
{
    use LinkPlaceholderTrait;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var GroupRepository
     */
    private $groupRepository;

    /**
     * @param UrlInterface $urlBuilder
     * @param GroupRepository $groupRepository
     */
    public function __construct(
        UrlInterface $urlBuilder,
        GroupRepository $groupRepository
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->groupRepository = $groupRepository;
    }

    /**
     * @inheritDoc
     */
    public function handle(DataObject $context)
    {
        $groupId = $context->getData('id');
        $groupName = $context->getData('text');
        if (!$groupId || !$groupName) {
            return null;
        }

        $group = $this->findGroupById($groupId);
        if (!$group) {
            return null;
        }

        $return = $this->buildLinkTag([
            'text' => $groupName,
            'title' => 'Edit this group in the admin',
            'href' => $this->urlBuilder->getUrl('customer/group/edit', [
                'id' => $group->getId(),
            ]),
            'target' => '_blank',
        ]);

        return $return;
    }

    /**
     * @param $id
     * @return GroupInterface|null
     */
    private function findGroupById($id)
    {
        try {
            return $this->groupRepository->getById($id);
        } catch (LocalizedException $e) {
            return null;
        }
    }
}
