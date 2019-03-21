<?php

namespace Ryvon\EventLog\Helper;

use Ryvon\EventLog\Helper\Group\GroupInterface;
use Ryvon\EventLog\Model\Config;
use Ryvon\EventLog\Model\Digest;
use Ryvon\EventLog\Model\EntryRepository;

class GroupBuilder
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var EntryRepository
     */
    private $entryRepository;

    /**
     * @var GroupSorter
     */
    private $groupSorter;

    /**
     * @var GroupFinder
     */
    private $groupFinder;

    /**
     * @param Config $config
     * @param EntryRepository $entryRepository
     * @param GroupSorter $groupSorter
     * @param GroupFinder $groupFinder
     */
    public function __construct(
        Config $config,
        EntryRepository $entryRepository,
        GroupSorter $groupSorter,
        GroupFinder $groupFinder
    ) {
        $this->entryRepository = $entryRepository;
        $this->groupSorter = $groupSorter;
        $this->groupFinder = $groupFinder;
        $this->config = $config;
    }

    /**
     * @param Digest $digest
     * @return GroupInterface[]
     */
    public function buildGroups(Digest $digest): array
    {
        $return = [];
        $groups = $this->entryRepository->findGroupsInDigest($digest, $this->config->getHideDuplicateEntries());

        foreach ($groups as $groupId => $entryCollections) {
            $return[] = $this->groupFinder->getGroup($groupId, $entryCollections);
        }

        return $this->groupSorter->sort($return);
    }
}
