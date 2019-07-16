<?php

namespace Ryvon\EventLog\Helper;

use Magento\Framework\DataObjectFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\MailException;
use Psr\Log\LoggerInterface;
use Ryvon\EventLog\Model\Digest;
use Ryvon\EventLog\Model\DigestRepository;

/**
 * Helper class to finish and send the digest.
 */
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
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var EmailBuilder
     */
    private $emailHelper;

    /**
     * @var DigestRepository
     */
    private $digestRepository;

    /**
     * @param LoggerInterface $logger
     * @param ManagerInterface $eventManager
     * @param DataObjectFactory $dataObjectFactory
     * @param DigestRepository $digestRepository
     * @param EmailBuilder $emailHelper
     */
    public function __construct(
        LoggerInterface $logger,
        ManagerInterface $eventManager,
        DataObjectFactory $dataObjectFactory,
        DigestRepository $digestRepository,
        EmailBuilder $emailHelper
    ) {
        $this->eventManager = $eventManager;
        $this->digestRepository = $digestRepository;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->emailHelper = $emailHelper;
        $this->logger = $logger;
    }

    /**
     * Finish the digest and create a new one.
     *
     * @param Digest $digest
     * @return bool
     */
    public function finishDigest(Digest $digest): bool
    {
        if (!$digest->getFinishedAt()) {
            if (!$this->digestRepository->finishDigest($digest)) {
                $this->logger->critical('Failed to finish digest');
                return false;
            }

            if (!$this->digestRepository->createNewDigest()) {
                $this->logger->critical('Failed to create next digest');
                // Don't return false, whatever is asking us to finish the digest doesn't care whether the next was
                // created or not.
            }

            $this->eventManager->dispatch('event_log_digest_finished', ['digest' => $digest]);
        }

        return true;
    }

    /**
     * Send the digest email.
     *
     * @param Digest $digest
     * @return bool
     */
    public function sendDigest(Digest $digest): bool
    {
        $builder = $this->emailHelper->createDigestEmail($digest);

        $eventData = $this->dataObjectFactory->create([
            'continue' => true,
            'digest' => $digest,
            'builder' => $builder,
        ]);

        $this->eventManager->dispatch('event_log_email_sending', ['data' => $eventData]);

        if (!$eventData->getData('continue')) {
            $this->logger->info('Cancelling email send due to "event_log_email_sending" event request.');
            return true;
        }

        try {
            $builder->getTransport()->sendMessage();
        } catch (MailException $e) {
            $this->logger->critical($e);
            return false;
        }
        return true;
    }
}
