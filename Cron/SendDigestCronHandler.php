<?php

namespace Ryvon\EventLog\Cron;

use Ryvon\EventLog\Helper\DigestSender;
use Ryvon\EventLog\Model\Config;
use Ryvon\EventLog\Model\DigestRepository;

/**
 * Cron handler to finish and send the digest.
 */
class SendDigestCronHandler
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var DigestRepository
     */
    private $digestRepository;

    /**
     * @var DigestSender
     */
    private $digestSender;

    /**
     * @param Config $config
     * @param DigestRepository $digestRepository
     * @param DigestSender $digestSender
     */
    public function __construct(
        Config $config,
        DigestRepository $digestRepository,
        DigestSender $digestSender
    ) {
        $this->config = $config;
        $this->digestRepository = $digestRepository;
        $this->digestSender = $digestSender;
    }

    /**
     * Executes the cron job, finishing and sending the digest depending on configuration.
     */
    public function execute()
    {
        if (!$this->config->getInternalDigestCron()) {
            return;
        }

        $digest = $this->digestRepository->findNewestUnfinishedDigest();
        if (!$digest) {
            return;
        }

        if (!$this->digestSender->finishDigest($digest)) {
            return;
        }

        if (!$this->config->getEnableDigestEmail() || !$this->config->getRecipients()) {
            return;
        }

        $this->digestSender->sendDigest($digest);
    }
}
