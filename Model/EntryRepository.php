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
     * @return Entry
     */
    public function create(): Entry
    {
        return $this->entryFactory->create();
    }

    /**
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
     * @param Digest $digest
     * @return Entry[]
     */
    public function findInDigest(Digest $digest): array
    {
        $collection = $this->entryCollectionFactory->create();

        $collection->removeAllFieldsFromSelect()->addFieldToSelect('entry_id');
        $collection->addFieldToFilter('digest_id', ['eq' => $digest->getId()]);
        $collection->setOrder('entry_group', DigestCollection::SORT_ORDER_ASC);
        $collection->addOrder('created_at', DigestCollection::SORT_ORDER_ASC);
        $collection->addOrder('entry_message', DigestCollection::SORT_ORDER_ASC);

        $entries = [];

        foreach ($collection as $entry) {
            // We load the entry outside of the collection so _afterLoad is called and the context
            // is run through json_decode properly
            $loaded = $this->getById($entry->getId());
            if ($loaded) {
                $entries[] = $loaded;
            }
        }

        return $entries;
    }
}
