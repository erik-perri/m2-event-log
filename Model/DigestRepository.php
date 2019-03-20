<?php

namespace Ryvon\EventLog\Model;

class DigestRepository
{
    /**
     * @var DigestCollectionFactory
     */
    private $digestCollectionFactory;

    /**
     * @var DigestFactory
     */
    private $digestFactory;

    /**
     * @var DigestResourceModel
     */
    private $digestResourceModel;

    /**
     * @param DigestFactory $digestFactory
     * @param DigestResourceModel $digestResourceModel
     */
    public function __construct(
        DigestCollectionFactory $digestCollectionFactory,
        DigestFactory $digestFactory,
        DigestResourceModel $digestResourceModel
    )
    {
        $this->digestFactory = $digestFactory;
        $this->digestResourceModel = $digestResourceModel;
        $this->digestCollectionFactory = $digestCollectionFactory;
    }

    /**
     * @param int $id
     * @return Digest|null
     */
    public function getById($id)
    {
        $digest = $this->digestFactory->create();
        $this->digestResourceModel->load($digest, $id);
        if ($digest->getId()) {
            return $digest;
        }
        return null;
    }

    /**
     * @param string $key
     * @return Digest|null
     */
    public function getByKey($key)
    {
        $digest = $this->digestFactory->create();
        $this->digestResourceModel->load($digest, $key, 'digest_key');
        if ($digest->getId()) {
            return $digest;
        }
        return null;
    }

    /**
     * @return Digest|null
     */
    public function createNewDigest()
    {
        $now = $this->getNow();
        if (!$now) {
            return null;
        }

        $digest = $this->digestFactory->create();
        $digest->setStartedAt($now);

        try {
            $this->digestResourceModel->save($digest);
        } catch (\Exception $e) {
            return null;
        }

        return $digest;
    }

    /**
     * @param Digest $digest
     * @return bool
     */
    public function finishDigest(Digest $digest): bool
    {
        if ($digest->getFinishedAt()) {
            return false;
        }

        $now = $this->getNow();
        if (!$now) {
            return false;
        }

        $digest->setFinishedAt($now);

        try {
            $this->digestResourceModel->save($digest);
        } catch (\Exception $e) {
            return null;
        }

        return true;
    }

    /**
     * @return Digest|null
     */
    public function findNewestUnfinishedDigest()
    {
        return $this->findFirst(function (DigestCollection $collection) {
            $collection->addFieldToSelect('*');
            $collection->addFieldToFilter('finished_at', ['null' => true]);
            $collection->setOrder('started_at', DigestCollection::SORT_ORDER_DESC);
        });
    }

    /**
     * @param string $startingDate
     * @return Digest|null
     */
    public function findPreviousDigest($startingDate)
    {
        return $this->findFirst(function (DigestCollection $collection) use ($startingDate) {
            $collection->addFieldToFilter('started_at', ['lt' => $startingDate]);
            $collection->setOrder('started_at', DigestCollection::SORT_ORDER_DESC);
        });
    }

    /**
     * @param string $startingDate
     * @return Digest|null
     */
    public function findNextDigest($startingDate)
    {
        return $this->findFirst(function (DigestCollection $collection) use ($startingDate) {
            $collection->addFieldToFilter('started_at', ['gt' => $startingDate]);
            $collection->setOrder('started_at', DigestCollection::SORT_ORDER_ASC);
        });
    }

    /**
     * @param callable $setupCollection
     * @return Digest|null
     */
    private function findFirst(callable $setupCollection)
    {
        $collection = $this->digestCollectionFactory->create();

        $setupCollection($collection);

        $collection->setPageSize(1)->setCurPage(1);

        $items = $collection->getItems();
        if (count($items)) {
            return reset($items);
        }

        return null;
    }

    /**
     * @return string|null
     */
    private function getNow()
    {
        try {
            return (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
        }
        return null;
    }
}
