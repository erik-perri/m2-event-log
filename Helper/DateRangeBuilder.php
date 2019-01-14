<?php

namespace Ryvon\EventLog\Helper;

use Magento\Framework\Stdlib\DateTime\Timezone;
use Ryvon\EventLog\Model\Digest;

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
     * @param Digest $digest
     * @return string
     */
    public function buildDateRange(Digest $digest)
    {
        $startedAt = $this->timezone->date($digest->getStartedAt());

        if ($digest->getFinishedAt() === null) {
            $now = $this->timezone->date();

            if ($now->format('Y-m-d') !== $startedAt->format('Y-m-d')) {
                return sprintf(
                    '<span class="date">%s %s</span> - <span class="date">%s, %s</span>',
                    $startedAt->format('F'),
                    $startedAt->format('jS'),
                    $now->format('jS'),
                    $now->format('Y')
                );
            }

            return sprintf(
                '<span class="date">%s</span>',
                $startedAt->format('F jS, Y')
            );
        }

        $finishedAt = $this->timezone->date($digest->getFinishedAt());

        if ($startedAt->format('Y-m-d') === $finishedAt->format('Y-m-d')) {
            return sprintf(
                '<span class="date">%s</span>',
                $startedAt->format('F jS, Y')
            );
        }

        if ($startedAt->format('Y-m') === $finishedAt->format('Y-m')) {
            return sprintf(
                '<span class="date">%s %s</span> - <span class="date">%s, %s</span>',
                $startedAt->format('F'),
                $startedAt->format('jS'),
                $finishedAt->format('jS'),
                $finishedAt->format('Y')
            );
        }

        return sprintf(
            '<span class="date">%s</span> - <span class="date">%s</span>',
            $startedAt->format('F jS, Y'),
            $finishedAt->format('F jS, Y')
        );
    }

    /**
     * @param Digest $digest
     * @return string
     */
    public function buildTimeRange(Digest $digest)
    {
        $startedAt = $this->timezone->date($digest->getStartedAt());

        if ($digest->getFinishedAt() === null) {
            return sprintf(
                '<span class="time">%s</span> to now',
                $startedAt->format('ga')
            );
        }

        $finishedAt = $this->timezone->date($digest->getFinishedAt());

        return sprintf(
            '<span class="time">%s</span> - <span class="time">%s</span>',
            $startedAt->format('ga'),
            $finishedAt->format('ga')
        );
    }
}
