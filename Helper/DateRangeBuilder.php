<?php

namespace Ryvon\EventLog\Helper;

use Magento\Framework\Stdlib\DateTime\Timezone;

class DateRangeBuilder
{
    /**
     * @var Timezone
     */
    private $timezone;

    /**
     * @var string
     */
    private $dateWrapper = '<span class="date">%s</span>';

    /**
     * @var string
     */
    private $timeWrapper = '<span class="time">%s</span>';

    /**
     * @param Timezone $timezone
     */
    public function __construct(Timezone $timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @return string
     */
    public function getDateWrapper(): string
    {
        return $this->dateWrapper;
    }

    /**
     * @param string $dateWrapper
     * @return DateRangeBuilder
     */
    public function setDateWrapper(string $dateWrapper): DateRangeBuilder
    {
        $this->dateWrapper = $dateWrapper;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimeWrapper(): string
    {
        return $this->timeWrapper;
    }

    /**
     * @param string $timeWrapper
     * @return DateRangeBuilder
     */
    public function setTimeWrapper(string $timeWrapper): DateRangeBuilder
    {
        $this->timeWrapper = $timeWrapper;
        return $this;
    }

    /**
     * @param \DateTime|string $startedAt
     * @param \DateTime|string|null $finishedAt
     * @return string
     */
    public function buildDateRange($startedAt, $finishedAt): string
    {
        $startedAtLocal = $this->timezone->date($startedAt);

        if ($finishedAt === null) {
            $now = $this->timezone->date();

            if ($now->format('Y-m-d') !== $startedAtLocal->format('Y-m-d')) {
                return sprintf(
                    '%s - %s',
                    $this->wrapDate(sprintf('%s %s', $startedAtLocal->format('F'), $startedAtLocal->format('jS'))),
                    $this->wrapDate(sprintf('%s, %s', $now->format('jS'), $now->format('Y')))
                );
            }

            return sprintf(
                '%s',
                $this->wrapDate($startedAtLocal->format('F jS, Y'))
            );
        }

        $finishedAtLocal = $this->timezone->date($finishedAt);

        if ($startedAtLocal->format('Y-m-d') === $finishedAtLocal->format('Y-m-d')) {
            return sprintf(
                '%s',
                $this->wrapDate($startedAtLocal->format('F jS, Y'))
            );
        }

        if ($startedAtLocal->format('Y-m') === $finishedAtLocal->format('Y-m')) {
            return sprintf(
                '%s - %s',
                $this->wrapDate(sprintf('%s %s', $startedAtLocal->format('F'), $startedAtLocal->format('jS'))),
                $this->wrapDate(sprintf('%s, %s', $finishedAtLocal->format('jS'), $finishedAtLocal->format('Y')))
            );
        }

        return sprintf(
            '%s - %s',
            $this->wrapDate($startedAtLocal->format('F jS, Y')),
            $this->wrapDate($finishedAtLocal->format('F jS, Y'))
        );
    }

    /**
     * @param \DateTime|string $startedAt
     * @param \DateTime|string|null $finishedAt
     * @return string
     */
    public function buildTimeRange($startedAt, $finishedAt): string
    {
        $startedAtLocal = $this->timezone->date($startedAt);

        if ($finishedAt === null) {
            return sprintf(
                '%s to now',
                $this->wrapTime($startedAtLocal->format('ga'))
            );
        }

        $finishedAtLocal = $this->timezone->date($finishedAt);

        return sprintf(
            '%s - %s',
            $this->wrapTime($startedAtLocal->format('ga')),
            $this->wrapTime($finishedAtLocal->format('ga'))
        );
    }

    /**
     * @param string $time
     * @return string
     */
    private function wrapDate(string $time): string
    {
        return sprintf($this->getDateWrapper(), $time);
    }

    /**
     * @param string $time
     * @return string
     */
    private function wrapTime(string $time): string
    {
        return sprintf($this->getTimeWrapper(), $time);
    }
}
