<?php

namespace Ryvon\EventLog\Helper;

use Magento\Framework\DataObjectFactory;
use Ryvon\EventLog\Model\Digest;
use Ryvon\EventLog\Model\DigestRepository;
use Ryvon\EventLog\Model\EntryRepository;

class DigestHelper
{
    const LEVEL_INFO = 'info';
    const LEVEL_ERROR = 'error';
    const LEVEL_WARNING = 'warning';
    const LEVEL_SECURITY = 'security';

    /**
     * @var DigestRepository
     */
    private $digestRepository;

    /**
     * @var EntryRepository
     */
    private $entryRepository;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param DigestRepository $digestRepository
     * @param EntryRepository $entryRepository
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        DigestRepository $digestRepository,
        EntryRepository $entryRepository,
        DataObjectFactory $dataObjectFactory
    )
    {
        $this->digestRepository = $digestRepository;
        $this->entryRepository = $entryRepository;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * @return Digest|null
     */
    public function findUnfinishedDigest()
    {
        return $this->digestRepository->findNewestUnfinishedDigest();
    }

    /**
     * @return string|null
     */
    protected function getNow()
    {
        try {
            return (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
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

        $digest = $this->digestRepository->create();
        $digest->setStartedAt($now);
        if (!$this->digestRepository->save($digest)) {
            return null;
        }

        return $digest;
    }

    /**
     * @param Digest $digest
     * @return bool
     */
    public function finishDigest(Digest $digest)
    {
        if ($digest->getFinishedAt()) {
            return false;
        }

        $now = $this->getNow();
        if (!$now) {
            return false;
        }

        $digest->setFinishedAt($now);
        if (!$this->digestRepository->save($digest)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $group
     * @param string $message
     * @param array $context
     * @return DigestHelper
     */
    public function addError($group, $message, $context)
    {
        return $this->addEntry(null, static::LEVEL_ERROR, $group, $message, $context);
    }

    /**
     * @param string $group
     * @param string $message
     * @param array $context
     * @return DigestHelper
     */
    public function addInfo($group, $message, $context)
    {
        return $this->addEntry(null, static::LEVEL_INFO, $group, $message, $context);
    }

    /**
     * @param string $group
     * @param string $message
     * @param array $context
     * @return DigestHelper
     */
    public function addWarning($group, $message, $context)
    {
        return $this->addEntry(null, static::LEVEL_WARNING, $group, $message, $context);
    }

    /**
     * @param string $group
     * @param string $message
     * @param array $context
     * @return DigestHelper
     */
    public function addSecurity($group, $message, $context)
    {
        return $this->addEntry(null, static::LEVEL_SECURITY, $group, $message, $context);
    }

    /**
     * @param Digest|null $digest
     * @param string $level
     * @param string $group
     * @param string $message
     * @param array $context
     * @param string $date
     * @return DigestHelper
     */
    public function addEntry($digest, $level, $group, $message, $context, $date = null)
    {
        if ($digest && !($digest instanceof Digest)) {
            throw new \InvalidArgumentException('$digest must be instance of Digest');
        }

        if (!$digest) {
            $digest = $this->findUnfinishedDigest();
            if (!$digest) {
                $digest = $this->createNewDigest();
            }
            if (!$digest) {
                return $this;
            }
        }

        $entry = $this->entryRepository->create();
        $entry->setDigestId($digest->getId())
            ->setEntryGroup($group)
            ->setEntryLevel($level)
            ->setEntryMessage($message)
            ->setEntryContext($this->dataObjectFactory->create(['data' => $context]))
            ->setCreatedAt($date);
        $this->entryRepository->save($entry);

        return $this;
    }
}
