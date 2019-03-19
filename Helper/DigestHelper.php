<?php

namespace Ryvon\EventLog\Helper;

use Ryvon\EventLog\Model\Digest;
use Ryvon\EventLog\Model\DigestRepository;

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
     * @param DigestRepository $digestRepository
     */
    public function __construct(DigestRepository $digestRepository)
    {
        $this->digestRepository = $digestRepository;
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
        if (!$this->digestRepository->save($digest)) {
            return false;
        }

        return true;
    }
}
