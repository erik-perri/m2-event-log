<?php

namespace Ryvon\EventLog\Model;

use Psr\Log\LoggerInterface;

class DigestRepository
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DigestFactory
     */
    private $digestFactory;

    /**
     * @var DigestResourceModel
     */
    private $digestResourceModel;

    /**
     * @var DigestCollectionFactory
     */
    private $digestCollectionFactory;

    /**
     * @param LoggerInterface $logger
     * @param DigestFactory $digestFactory
     * @param DigestResourceModel $digestResourceModel
     * @param DigestCollectionFactory $digestCollectionFactory
     */
    public function __construct(
        LoggerInterface $logger,
        DigestFactory $digestFactory,
        DigestResourceModel $digestResourceModel,
        DigestCollectionFactory $digestCollectionFactory
    )
    {
        $this->logger = $logger;
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
     * @return Digest
     */
    public function create()
    {
        return $this->digestFactory->create();
    }

    /**
     * @param Digest $digest
     * @return bool
     */
    public function save(Digest $digest)
    {
        try {
            $this->digestResourceModel->save($digest);
            return true;
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return false;
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
    protected function findFirst(callable $setupCollection)
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
}
