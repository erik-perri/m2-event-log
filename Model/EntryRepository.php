<?php

namespace Ryvon\EventLog\Model;

use Psr\Log\LoggerInterface;

class EntryRepository
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EntryFactory
     */
    private $entryFactory;

    /**
     * @var EntryResourceModel
     */
    private $entryResourceModel;

    /**
     * @var EntryCollectionFactory
     */
    private $entryCollectionFactory;

    /**
     * @param LoggerInterface $logger
     * @param EntryFactory $entryFactory
     * @param EntryResourceModel $entryResourceModel
     * @param EntryCollectionFactory $entryCollectionFactory
     */
    public function __construct(
        LoggerInterface $logger,
        EntryFactory $entryFactory,
        EntryResourceModel $entryResourceModel,
        EntryCollectionFactory $entryCollectionFactory
    )
    {
        $this->logger = $logger;
        $this->entryFactory = $entryFactory;
        $this->entryResourceModel = $entryResourceModel;
        $this->entryCollectionFactory = $entryCollectionFactory;
    }

    /**
     * Retrieve the specified entry
     *
     * @param int $id
     * @return Entry|null
     */
    public function getById($id)
    {
        $entry = $this->entryFactory->create();
        $this->entryResourceModel->load($entry, $id);
        if ($entry->getEntryId()) {
            return $entry;
        }
        return null;
    }

    /**
     * Create a new entry model
     *
     * @return Entry
     */
    public function create(): Entry
    {
        return $this->entryFactory->create();
    }

    /**
     * Save an entry model
     *
     * @param Entry $entry
     * @return bool
     */
    public function save(Entry $entry): bool
    {
        try {
            $this->entryResourceModel->save($entry);
            return true;
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return false;
    }

    /**
     * Find entries in the specified digest
     *
     * @param Digest $digest
     * @return EntryCollection
     */
    public function findInDigest(Digest $digest): EntryCollection
    {
        $collection = $this->entryCollectionFactory->create();

        $collection->setHideDuplicates(false);
        $collection->removeAllFieldsFromSelect()->addFieldToSelect(['entry_id', 'entry_group', 'entry_level']);
        $collection->addFieldToFilter('digest_id', ['eq' => $digest->getId()]);
        $collection->setOrder('entry_group', DigestCollection::SORT_ORDER_ASC);
        $collection->addOrder('created_at', DigestCollection::SORT_ORDER_ASC);
        $collection->addOrder('entry_message', DigestCollection::SORT_ORDER_ASC);

        return $collection;
    }

    /**
     * Find the entries in the group
     *
     * @param Digest $digest
     * @param bool $hideDuplicates
     * @return EntryCollection[]
     */
    public function findGroupsInDigest(Digest $digest, bool $hideDuplicates): array
    {
        $entries = $this->findInDigest($digest);
        if (!$entries || !$entries->count()) {
            return [];
        }

        $return = [];
        $groupedEntries = [];

        foreach ($entries as $entry) {
            $groupedEntries[$entry->getEntryGroup()][] = $entry->getEntryId();
        }

        foreach ($groupedEntries as $groupId => $groupEntryId) {
            $collection = $this->entryCollectionFactory->create();

            $collection->setHideDuplicates($hideDuplicates);
            $collection->removeAllFieldsFromSelect()->addFieldToSelect('*');
            $collection->addFieldToFilter('entry_id', ['in' => $groupEntryId]);
            $collection->setOrder('entry_group', DigestCollection::SORT_ORDER_ASC);
            $collection->addOrder('created_at', DigestCollection::SORT_ORDER_ASC);
            $collection->addOrder('entry_message', DigestCollection::SORT_ORDER_ASC);

            $return[$groupId] = $collection;
        }

        return $return;
    }
}
