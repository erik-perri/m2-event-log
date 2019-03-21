<?php

namespace Ryvon\EventLog\Observer;

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

class RecordEntryObserver implements ObserverInterface
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
    )
    {
        $this->digestRepository = $digestRepository;
        $this->logger = $logger;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->entryRepository = $entryRepository;
        $this->placeholderReplacer = $placeholderReplacer;
        $this->objectManager = $objectManager;
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
            $userContext = [];

            if (PHP_SAPI === 'cli') {
                $userContext = [
                    'user-name' => sprintf('%s (CLI)', get_current_user()),
                    'user-ip' => '127.0.0.1',
                ];
            } else {
                $user = $observer->getData('user');
                if ($group === 'admin' || $user) {
                    // This class relies on Session which will not work unless an area code is set (we are not running a
                    // CLI command).   We initialize after so CLI commands can trigger logs.
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
                ->setCreatedAt($observer->getData('date') ?? null);

            $this->entryRepository->save($entry);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
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
