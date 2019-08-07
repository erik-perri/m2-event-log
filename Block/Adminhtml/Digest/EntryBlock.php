<?php

namespace Ryvon\EventLog\Block\Adminhtml\Digest;

use Ryvon\EventLog\Block\Adminhtml\TemplateBlock;
use Ryvon\EventLog\Helper\DigestRequestHelper;
use Ryvon\EventLog\Model\Digest;
use Ryvon\EventLog\Model\Entry;
use Magento\Backend\Block\Template;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Ryvon\EventLog\Placeholder\PlaceholderProcessor;

/**
 * Block class for the default entry block for both the administrator and email.
 */
class EntryBlock extends TemplateBlock
{
    /**
     * @var DigestRequestHelper
     */
    private $digestRequestHelper;

    /**
     * @var PlaceholderProcessor
     */
    private $placeholderProcessor;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var Digest
     */
    private $currentDigest;

    /**
     * @param DigestRequestHelper $digestRequestHelper
     * @param PlaceholderProcessor $placeholderProcessor
     * @param TimezoneInterface $timezone
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        DigestRequestHelper $digestRequestHelper,
        PlaceholderProcessor $placeholderProcessor,
        TimezoneInterface $timezone,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->digestRequestHelper = $digestRequestHelper;
        $this->placeholderProcessor = $placeholderProcessor;
        $this->timezone = $timezone;
    }

    /**
     * Helper function to retrieve the timezone helper.
     *
     * @return TimezoneInterface
     */
    public function getTimezone(): TimezoneInterface
    {
        return $this->timezone;
    }

    /**
     * Generates a row class for the log row.
     *
     * @param Entry $entry
     * @param bool $includeOdd
     * @return string
     */
    public function getRowClass(Entry $entry, $includeOdd = true): string
    {
        if (!$entry) {
            $entry = $this->getEntry();
        }

        $classes = [];

        if ($entry) {
            $classes[] = trim(preg_replace('/[^[:alnum:]]/u', '-', $entry->getEntryLevel()), "\t\n\r\0\x0B-");
        }

        if ($includeOdd && $this->getData('odd')) {
            $classes[] = '_odd-row';
        }

        return implode(' ', $classes);
    }

    /**
     * Retrieves the entry assigned to the block
     *
     * @return Entry|null
     */
    public function getEntry()
    {
        return $this->getData('entry') ?: null;
    }

    /**
     * Formats the specified time, including the day if the digest spans multiple days.
     *
     * @param \DateTime $dateTime
     * @return string
     */
    public function formatLogTime($dateTime): string
    {
        if (!$dateTime) {
            return '';
        }

        $format = 'h:i A';
        if ($this->digestSpansMultipleDays()) {
            $format = 'M d, h:i A';
        }

        return $this->getTimezone()->date($dateTime)->format($format);
    }

    /**
     * Checks if the current digest spans multiple days.
     *
     * @return bool
     */
    private function digestSpansMultipleDays(): bool
    {
        $digest = $this->getDigest();
        if (!$digest) {
            return false;
        }

        $startedAt = $digest->getStartedAtDateTime();
        $finishedAt = $digest->getFinishedAtDateTime();

        $startedAt = $this->timezone->date($startedAt);
        $compareTo = $this->timezone->date($finishedAt);

        return $startedAt->format('Y-m-d') !== $compareTo->format('Y-m-d');
    }

    /**
     * Retrieves the current digest from the current request, using the newest if none is specified.
     *
     * @return Digest|null
     */
    public function getDigest()
    {
        // TODO Change this to getData and setData
        if ($this->currentDigest === null) {
            $this->currentDigest = $this->digestRequestHelper->getCurrentDigest($this->getRequest());
        }
        return $this->currentDigest;
    }

    /**
     * Formats the specified time for the title attribute.
     *
     * @param \DateTime $dateTime
     * @return string
     */
    public function formatTitleTime($dateTime): string
    {
        if (!$dateTime) {
            return '';
        }

        $format = 'l F jS, h:i:s A';
        return $this->timezone->date($dateTime)->format($format);
    }

    /**
     * Replaces the placeholders in the specified message using the specified context.
     *
     * @param string $message
     * @param DataObject|array $context
     * @return string
     */
    public function replacePlaceholders(string $message, $context): string
    {
        return $this->placeholderProcessor->process($message, $context);
    }
}
