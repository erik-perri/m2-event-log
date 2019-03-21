<?php

namespace Ryvon\EventLog\Helper;

use Ryvon\EventLog\Helper\Group\GroupInterface;
use Ryvon\EventLog\Helper\Group\MissingGroupFactory;
use Ryvon\EventLog\Model\EntryCollection;
use Magento\Framework\ObjectManagerInterface;

class GroupFinder
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MissingGroupFactory
     */
    private $missingGroupFactory;

    /**
     * @var string[]
     */
    private $groups = [];

    /**
     * @param ObjectManagerInterface $objectManager
     * @param MissingGroupFactory $missingGroupFactory
     * @param array $groups
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        MissingGroupFactory $missingGroupFactory,
        $groups = []
    ) {
        $this->objectManager = $objectManager;
        $this->missingGroupFactory = $missingGroupFactory;

        foreach ($groups as $id => $groupClass) {
            try {
                $class = new \ReflectionClass($groupClass);
                if ($class->implementsInterface(GroupInterface::class)) {
                    $this->addGroup($id, $groupClass);
                }
            } catch (\ReflectionException $e) {
            }
        }
    }

    /**
     * @param string $groupId
     * @param string $groupClass
     * @return GroupFinder
     */
    public function addGroup(string $groupId, string $groupClass): GroupFinder
    {
        $this->groups[$groupId] = $groupClass;
        return $this;
    }

    /**
     * @param string $groupId
     * @return GroupFinder
     */
    public function removeGroup(string $groupId): GroupFinder
    {
        if (isset($this->groups[$groupId])) {
            unset($this->groups[$groupId]);
        }
        return $this;
    }

    /**
     * @param string $groupId
     * @param EntryCollection $entries
     * @return GroupInterface
     */
    public function getGroup(string $groupId, EntryCollection $entries): GroupInterface
    {
        $group = isset($this->groups[$groupId])
            ? $this->objectManager->create($this->groups[$groupId])
            : null;

        // If the group doesn't exist it is likely handled by a plugin that is no longer installed, we create a missing
        // group handler to render it like a log.
        if (!$group) {
            $group = $this->missingGroupFactory->create();
            $group->setTitle($this->convertIdToTitle($groupId));
        }

        return $group->setEntryCollection($entries);
    }

    /**
     * Convert snake or dash case to title case
     *
     * @param string $groupId
     * @return string
     */
    private function convertIdToTitle(string $groupId): string
    {
        $intermediate = preg_replace('/[\-_]/', ' ', $groupId);
        return ucwords(trim($intermediate));
    }
}
