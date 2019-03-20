<?php

namespace Ryvon\EventLog\Block\Adminhtml\Digest;

use Ryvon\EventLog\Helper\DigestRequestHelper;
use Ryvon\EventLog\Helper\PlaceholderReplacer;
use Ryvon\EventLog\Model\Digest;
use Ryvon\EventLog\Model\Entry;
use Magento\Backend\Block\Template;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\Timezone;

class EntryBlock extends Template
{
    /**
     * @var DigestRequestHelper
     */
    private $digestRequestHelper;

    /**
     * @var PlaceholderReplacer
     */
    private $placeholderReplacer;

    /**
     * @var Timezone
     */
    private $timezone;

    /**
     * @var Digest
     */
    private $currentDigest;

    /**
     * @param DigestRequestHelper $digestRequestHelper
     * @param PlaceholderReplacer $placeholderReplacer
     * @param Timezone $timezone
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        DigestRequestHelper $digestRequestHelper,
        PlaceholderReplacer $placeholderReplacer,
        Timezone $timezone,
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->digestRequestHelper = $digestRequestHelper;
        $this->placeholderReplacer = $placeholderReplacer;
        $this->timezone = $timezone;
    }

    /**
     * @return Timezone
     */
    public function getTimezone(): Timezone
    {
        return $this->timezone;
    }

    /**
     * @return Digest|null
     */
    public function getDigest()
    {
        if ($this->currentDigest === null) {
            $this->currentDigest = $this->digestRequestHelper->getCurrentDigest($this->getRequest());
        }
        return $this->currentDigest;
    }

    /**
     * @return Entry|null
     */
    public function getEntry()
    {
        return $this->getData('entry') ?: null;
    }

    /**
     * @param Entry $entry
     * @param bool $includeOdd
     * @return string
     */
    public function getRowClass($entry = null, $includeOdd = true): string
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
     * @param $mysqlTime
     * @return string
     */
    public function formatLogTime($mysqlTime): string
    {
        if (!$mysqlTime) {
            return '';
        }

        $format = 'h:i A';
        if ($this->digestSpansMultipleDays()) {
            $format = 'M d, h:i A';
        }

        return $this->timezone->date($mysqlTime)->format($format);
    }

    /**
     * @param $mysqlTime
     * @return string
     */
    public function formatTitleTime($mysqlTime): string
    {
        if (!$mysqlTime) {
            return '';
        }

        $format = 'l F jS, h:i:s A';
        return $this->timezone->date($mysqlTime)->format($format);
    }

    /**
     * @return bool
     */
    protected function digestSpansMultipleDays(): bool
    {
        $digest = $this->getDigest();
        if (!$digest) {
            return false;
        }

        $startedAt = $this->timezone->date($digest->getStartedAt());

        if ($digest->getFinishedAt()) {
            $compareTo = $this->timezone->date($digest->getFinishedAt());
        } else {
            // If the digest isn't finished we can compare to now
            $compareTo = $this->timezone->date();
        }

        return $startedAt->format('Y-m-d') !== $compareTo->format('Y-m-d');
    }

    /**
     * @param string $message
     * @param DataObject $context
     * @return string
     */
    public function replacePlaceholders($message, $context): string
    {
        return $this->placeholderReplacer->replace($message, $context);
    }
}
