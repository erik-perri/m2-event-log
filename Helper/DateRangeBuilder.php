<?php

namespace Ryvon\EventLog\Helper;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class DateRangeBuilder
{
    /**
     * @var TimezoneInterface
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
     * @param TimezoneInterface $timezone
     */
    public function __construct(TimezoneInterface $timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @param \DateTime $startedAt
     * @param \DateTime|null $finishedAt
     * @return string
     */
    public function buildDateRange($startedAt, $finishedAt): string
    {
        $startedAtLocal = $this->timezone->date($startedAt);
        if ($finishedAt === null) {
            $now = $this->timezone->date();
            if ($now->format('Y-m-d') !== $startedAtLocal->format('Y-m-d')) {
                return str_replace(
                    [
                        '{date-start}',
                        '{date-end}'
                    ],
                    [
                        $this->wrapDate(sprintf('%s %s', $startedAtLocal->format('F'), $startedAtLocal->format('jS'))),
                        $this->wrapDate(sprintf('%s, %s', $now->format('jS'), $now->format('Y')))
                    ],
                    __('{date-start} - {date-end}')
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

        $dateStart = $this->wrapDate($startedAtLocal->format('F jS, Y'));
        $dateEnd = $this->wrapDate($finishedAtLocal->format('F jS, Y'));

        if ($startedAtLocal->format('Y-m') === $finishedAtLocal->format('Y-m')) {
            $dateStart = $this->wrapDate(sprintf(
                '%s %s',
                $startedAtLocal->format('F'),
                $startedAtLocal->format('jS')
            ));
            $dateEnd = $this->wrapDate(sprintf(
                '%s, %s',
                $finishedAtLocal->format('jS'),
                $finishedAtLocal->format('Y')
            ));
        }

        return str_replace(
            ['{date-start}', '{date-end}'],
            [$dateStart, $dateEnd],
            __('{date-start} - {date-end}')
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
     * @param \DateTime $startedAt
     * @param \DateTime|null $finishedAt
     * @return string
     */
    public function buildTimeRange($startedAt, $finishedAt): string
    {
        $startedAtLocal = $this->timezone->date($startedAt);

        if ($finishedAt === null) {
            return str_replace(
                ['{time-start}'],
                [$this->wrapTime($startedAtLocal->format('ga'))],
                __('{time-start} to now')
            );
        }

        $finishedAtLocal = $this->timezone->date($finishedAt);

        return str_replace(
            [
                '{time-start}',
                '{time-end}'
            ],
            [
                $this->wrapTime($startedAtLocal->format('ga')),
                $this->wrapTime($finishedAtLocal->format('ga'))
            ],
            __('{time-start} - {time-end}')
        );
    }

    /**
     * @param string $time
     * @return string
     */
    private function wrapTime(string $time): string
    {
        return sprintf($this->getTimeWrapper(), $time);
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
}
