<?php

namespace Ryvon\EventLog\Cron;

use Ryvon\EventLog\Helper\DigestHelper;
use Ryvon\EventLog\Helper\DigestSender;
use Ryvon\EventLog\Model\Config;

class SendDigestCronHandler
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var DigestHelper
     */
    private $digestHelper;

    /**
     * @var DigestSender
     */
    private $digestSender;

    /**
     * @param Config $config
     * @param DigestHelper $digestHelper
     * @param DigestSender $digestSender
     */
    public function __construct(
        Config $config,
        DigestHelper $digestHelper,
        DigestSender $digestSender
    )
    {
        $this->config = $config;
        $this->digestHelper = $digestHelper;
        $this->digestSender = $digestSender;
    }

    /**
     * @return SendDigestCronHandler
     */
    public function execute()
    {
        if (!$this->config->getEnableDigestEmail() ||
            !$this->config->getInternalDigestCron()) {
            return $this;
        }

        $digest = $this->digestHelper->findUnfinishedDigest();
        if (!$digest) {
            return $this;
        }

        if (!$this->config->getRecipients()) {
            return $this;
        }

        if (!$this->digestSender->finishDigest($digest)) {
            return $this;
        }

        if ($this->config->getEnableDigestEmail()) {
            $this->digestSender->sendDigest($digest);
        }

        return $this;
    }
}
