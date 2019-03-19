<?php

namespace Ryvon\EventLog\Helper;

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\MailException;
use Psr\Log\LoggerInterface;
use Ryvon\EventLog\Model\Digest;

class DigestSender
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var EmailBuilder
     */
    private $emailHelper;

    /**
     * @var DigestHelper
     */
    private $digestHelper;

    /**
     * @param LoggerInterface $logger
     * @param ManagerInterface $eventManager
     * @param DigestHelper $digestHelper
     * @param EmailBuilder $emailHelper
     */
    public function __construct(
        LoggerInterface $logger,
        ManagerInterface $eventManager,
        DigestHelper $digestHelper,
        EmailBuilder $emailHelper
    )
    {
        $this->eventManager = $eventManager;
        $this->digestHelper = $digestHelper;
        $this->emailHelper = $emailHelper;
        $this->logger = $logger;
    }

    /**
     * @param Digest $digest
     * @return bool
     */
    public function finishDigest(Digest $digest): bool
    {
        if (!$digest->getFinishedAt()) {
            if (!$this->digestHelper->finishDigest($digest)) {
                $this->logger->critical('Failed to finish digest');
                return false;
            }

            $this->eventManager->dispatch('event_log_post_finish_digest', [
                'digest' => $digest,
            ]);

            if (!$this->digestHelper->createNewDigest()) {
                $this->logger->critical('Failed to create next digest');
                // Don't return false, whatever is asking us to finish the digest
                // doesn't care whether the next was created or not.
            }
        }

        return true;
    }

    /**
     * @param Digest $digest
     * @return bool
     */
    public function sendDigest(Digest $digest): bool
    {
        $builder = $this->emailHelper->createDigestEmail($digest);

        $this->eventManager->dispatch('event_log_pre_send_digest', [
            'instance' => $this,
            'digest' => $digest,
            'builder' => $builder,
        ]);

        try {
            $builder->getTransport()->sendMessage();
        } catch (MailException $e) {
            $this->logger->critical($e);
            return false;
        }
        return true;
    }
}
