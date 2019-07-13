<?php

namespace Ryvon\EventLog\Observer;

use Exception;
use Magento\Framework\Event;
use Ryvon\EventLog\Helper\PlaceholderReplacer;
use Ryvon\EventLog\Helper\UserContextHelper;
use Ryvon\EventLog\Model\Digest;
use Ryvon\EventLog\Model\DigestRepository;
use Ryvon\EventLog\Model\EntryRepository;
use Magento\Framework\DataObject;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\User\Model\User;
use Psr\Log\LoggerInterface;

/**
 * Event observer to handle the various event_log_[...] events.
 */
class AddEntryObserver implements ObserverInterface
{
    /**
     * @var DigestRepository
     */
    private $digestRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

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
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param DigestRepository $digestRepository
     * @param LoggerInterface $logger
     * @param DataObjectFactory $dataObjectFactory
     * @param EntryRepository $entryRepository
     * @param PlaceholderReplacer $placeholderReplacer
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        DigestRepository $digestRepository,
        LoggerInterface $logger,
        DataObjectFactory $dataObjectFactory,
        EntryRepository $entryRepository,
        PlaceholderReplacer $placeholderReplacer,
        ObjectManagerInterface $objectManager
    ) {
        $this->digestRepository = $digestRepository;
        $this->logger = $logger;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->entryRepository = $entryRepository;
        $this->placeholderReplacer = $placeholderReplacer;
        $this->objectManager = $objectManager;
    }

    /**
     * Adds an event log based on the event context.
     *
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
            $userContext = [];

            if (PHP_SAPI === 'cli') {
                $userContext = [
                    'user-name' => sprintf('%s (CLI)', get_current_user()),
                    'user-ip' => '127.0.0.1',
                ];
            } else {
                $user = $observer->getData('user');
                if ($group === 'admin' || $user) {
                    // This class relies on Session which will not work unless an area code is set (which only happens
                    // when we are not running a CLI command).   We load using ObjectManager to give CLI commands
                    // leaving logs a chance to set an area code before doing so.
                    /** @var UserContextHelper $helper */
                    $helper = $this->objectManager->get(UserContextHelper::class);
                    if ($helper) {
                        if ($user instanceof User) {
                            $userContext = $helper->getContextFromUser($user);
                        } else {
                            $userContext = $helper->getContextFromCurrentUser();
                        }
                    }
                }
            }

            $context = array_merge($context, $userContext);

            $context = $this->dataObjectFactory->create(['data' => $context]);
            if (!$this->checkMessageSanity($message, $context)) {
                $this->logger->error(
                    'Entry does not contain the proper context to render without placeholder processors.',
                    [
                        'message' => $message,
                    ]
                );
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
                ->setCreatedAt($observer->getData('date') ?: null);

            $this->entryRepository->save($entry);
        } catch (Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Retrieves the entry level from the event name.
     *
     * @param Event $event
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

        return preg_replace($match, '$1', $event->getName());
    }

    /**
     * Checks if the message can be rendered with no placeholders loaded.
     *
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
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves the current digest or creates a new one.
     *
     * @return Digest|null
     */
    private function findOrCreateDigest()
    {
        $digest = $this->digestRepository->findNewestUnfinishedDigest();
        if (!$digest) {
            $digest = $this->digestRepository->createNewDigest();
        }

        return $digest;
    }
}
