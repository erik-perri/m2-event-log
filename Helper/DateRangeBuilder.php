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
     * @param Timezone $timezone
     */
    public function __construct(Timezone $timezone)
    {
        $this->timezone = $timezone;
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
                    '<span class="date">%s %s</span> - <span class="date">%s, %s</span>',
                    $startedAtLocal->format('F'),
                    $startedAtLocal->format('jS'),
                    $now->format('jS'),
                    $now->format('Y')
                );
            }

            return sprintf(
                '<span class="date">%s</span>',
                $startedAtLocal->format('F jS, Y')
            );
        }

        $finishedAtLocal = $this->timezone->date($finishedAt);

        if ($startedAtLocal->format('Y-m-d') === $finishedAtLocal->format('Y-m-d')) {
            return sprintf(
                '<span class="date">%s</span>',
                $startedAtLocal->format('F jS, Y')
            );
        }

        if ($startedAtLocal->format('Y-m') === $finishedAtLocal->format('Y-m')) {
            return sprintf(
                '<span class="date">%s %s</span> - <span class="date">%s, %s</span>',
                $startedAtLocal->format('F'),
                $startedAtLocal->format('jS'),
                $finishedAtLocal->format('jS'),
                $finishedAtLocal->format('Y')
            );
        }

        return sprintf(
            '<span class="date">%s</span> - <span class="date">%s</span>',
            $startedAtLocal->format('F jS, Y'),
            $finishedAtLocal->format('F jS, Y')
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
                '<span class="time">%s</span> to now',
                $startedAtLocal->format('ga')
            );
        }

        $finishedAtLocal = $this->timezone->date($finishedAt);

        return sprintf(
            '<span class="time">%s</span> - <span class="time">%s</span>',
            $startedAtLocal->format('ga'),
            $finishedAtLocal->format('ga')
        );
    }
}
