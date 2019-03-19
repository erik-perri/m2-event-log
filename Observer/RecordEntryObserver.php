<?php

namespace Ryvon\EventLog\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\User\Model\User;
use Ryvon\EventLog\Helper\PlaceholderReplacer;
use Ryvon\EventLog\Model\Digest;
use Ryvon\EventLog\Model\EntryRepository;
use Psr\Log\LoggerInterface;
use Ryvon\EventLog\Helper\DigestHelper;
use Ryvon\EventLog\Helper\Group\AdminGroup;
use Ryvon\EventLog\Helper\UserContextHelper;

class RecordEntryObserver implements ObserverInterface
{
    /**
     * @var DigestHelper
     */
    private $digestHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UserContextHelper
     */
    private $userContextHelper;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var EntryRepository
     */
    private $entryRepository;

    /**
     * @var PlaceholderReplacer
     */
    private $placeholderReplacer;

    /**
     * @param DigestHelper $helper
     * @param LoggerInterface $logger
     * @param UserContextHelper $userContextHelper
     * @param DataObjectFactory $dataObjectFactory
     * @param EntryRepository $entryRepository
     * @param PlaceholderReplacer $placeholderReplacer
     */
    public function __construct(
        DigestHelper $helper,
        LoggerInterface $logger,
        UserContextHelper $userContextHelper,
        DataObjectFactory $dataObjectFactory,
        EntryRepository $entryRepository,
        PlaceholderReplacer $placeholderReplacer
    )
    {
        $this->digestHelper = $helper;
        $this->logger = $logger;
        $this->userContextHelper = $userContextHelper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->entryRepository = $entryRepository;
        $this->placeholderReplacer = $placeholderReplacer;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $level = $this->getEntryLevelFromEventName($observer->getEvent());
            if (!$level) {
                return;
            }

            $message = $observer->getData('message');
            if (!$message) {
                return;
            }

            $group = $observer->getData('group') ?: '';
            $context = $observer->getData('context') ?: [];

            $user = $observer->getData('user');
            if ($group === AdminGroup::GROUP_ID || $observer->getData('user')) {
                if ($user instanceof User) {
                    $context = $this->userContextHelper->getContextFromUser($user, $context);
                } else {
                    $context = $this->userContextHelper->getContextFromCurrentUser($context);
                }
            }

            $context = $this->dataObjectFactory->create(['data' => $context]);
            if (!$this->checkMessageSanity($message, $context)) {
                $this->logger->error('Entry does not contain the proper context to render without placeholder processors.', [
                    'message' => $message,
                ]);
                return;
            }

            $digest = $observer->getData('digest');
            if ($digest && !($digest instanceof Digest)) {
                $this->logger->error('Invalid digest supplied.', [
                    'message' => $message,
                ]);
                return;
            }

            if (!$digest) {
                $digest = $this->findOrCreateDigest();
            }

            if (!$digest) {
                $this->logger->error('Failed to find or create digest.', [
                    'message' => $message,
                ]);
                return;
            }

            $entry = $this->entryRepository->create();

            $entry->setDigestId($digest->getId())
                ->setEntryGroup($group)
                ->setEntryLevel($level)
                ->setEntryMessage($message)
                ->setEntryContext($context)
                ->setCreatedAt($observer->getData('date') ?? null);

            $this->entryRepository->save($entry);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @return Digest|null
     */
    private function findOrCreateDigest()
    {
        $digest = $this->digestHelper->findUnfinishedDigest();
        if (!$digest) {
            $digest = $this->digestHelper->createNewDigest();
        }

        return $digest;
    }

    /**
     * @param string $message
     * @param DataObject $context
     * @return bool
     */
    private function checkMessageSanity(string $message, DataObject $context): bool
    {
        try {
            $replaced = $this->placeholderReplacer->replace($message, $context, true);
            if (strpos($replaced, $this->placeholderReplacer->getUnknownText()) !== false) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param \Magento\Framework\Event $event
     * @return string|null
     */
    private function getEntryLevelFromEventName($event)
    {
        if (!$event || !$event->getName()) {
            return null;
        }

        $match = '#^event_log_([a-z]+)$#i';
        if (!preg_match($match, $event->getName())) {
            return null;
        }

        return preg_replace('#^event_log_([a-z]+)$#i', '$1', $event->getName());
    }
}
