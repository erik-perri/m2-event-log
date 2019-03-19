<?php

namespace Ryvon\EventLog\Helper;

use Ryvon\EventLog\Helper\Group\GroupInterface;
use Ryvon\EventLog\Helper\Group\MissingGroup;
use Ryvon\EventLog\Helper\Group\MissingGroupFactory;

class GroupFinder
{
    /**
     * @var MissingGroupFactory
     */
    private $missingGroupFactory;

    /**
     * @var GroupInterface[]
     */
    private $groups = [];

    /**
     * @param MissingGroupFactory $missingGroupFactory
     * @param array $groups
     */
    public function __construct(MissingGroupFactory $missingGroupFactory, $groups = [])
    {
        $this->missingGroupFactory = $missingGroupFactory;

        foreach ($groups as $group) {
            if ($group instanceof GroupInterface) {
                $this->addGroup($group);
            }
        }
    }

    /**
     * @param GroupInterface $group
     * @return GroupFinder
     */
    public function addGroup(GroupInterface $group): GroupFinder
    {
        $this->groups[$group->getId()] = $group;
        return $this;
    }

    /**
     * @param GroupInterface $group
     * @return GroupFinder
     */
    public function removeGroup(GroupInterface $group): GroupFinder
    {
        if (isset($this->groups[$group->getId()])) {
            unset($this->groups[$group->getId()]);
        }
        return $this;
    }

    /**
     * @param string $groupId
     * @return GroupInterface|null
     */
    public function findGroup($groupId)
    {
        return $this->groups[$groupId] ?? null;
    }

    /**
     * @param $groupId
     * @return GroupInterface
     */
    public function addMissingGroup($groupId): GroupInterface
    {
        if ($this->findGroup($groupId)) {
            return $this->findGroup($groupId);
        }

        // Convert snake or dash case to title case
        $intermediate = preg_replace('/[\-_]/', ' ', $groupId);
        $title = ucwords(trim($intermediate)) . ' <!-- Missing renderer: ' . $groupId . ' -->';

        /** @var MissingGroup $missingGroup */
        $missingGroup = $this->missingGroupFactory->create();
        $missingGroup->setId($groupId)->setTitle($title);

        $this->addGroup($missingGroup);

        return $missingGroup;
    }
}
