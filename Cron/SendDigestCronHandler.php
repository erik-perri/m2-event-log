<?php

namespace Ryvon\EventLog\Cron;

use Ryvon\EventLog\Helper\DigestSender;
use Ryvon\EventLog\Model\Config;
use Ryvon\EventLog\Model\DigestRepository;

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
    )
    {
        $this->config = $config;
        $this->digestRepository = $digestRepository;
        $this->digestSender = $digestSender;
    }

    /**
     * @return SendDigestCronHandler
     */
    public function execute(): SendDigestCronHandler
    {
        if (!$this->config->getEnableDigestEmail() ||
            !$this->config->getInternalDigestCron()) {
            return $this;
        }

        $digest = $this->digestRepository->findNewestUnfinishedDigest();
        if (!$digest) {
            return $this;
        }

        if (!$this->digestSender->finishDigest($digest)) {
            return $this;
        }

        if (!$this->config->getRecipients()) {
            return $this;
        }

        if ($this->config->getEnableDigestEmail()) {
            $this->digestSender->sendDigest($digest);
        }

        return $this;
    }
}
