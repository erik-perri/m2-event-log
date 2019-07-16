<?php

namespace Ryvon\EventLog\Helper;

/**
 * Helper class to maintain the CSS files used by the log.
 *
 * This is needed to let other plugins add their CSS to the page and email easily.
 */
class CssHelper
{
    /**
     * @var array
     */
    private $commonCss;

    /**
     * @var array
     */
    private $emailCss;

    /**
     * @param array $commonCss
     * @param array $emailCss
     */
    public function __construct(
        array $commonCss = [],
        array $emailCss = []
    ) {
        $this->commonCss = array_merge(['Ryvon_EventLog::css/styles.css'], $commonCss);
        $this->emailCss = array_merge(['Ryvon_EventLog::css/email.css'], $emailCss);
    }

    /**
     * Returns the CSS file that should render on both the admin and email.
     *
     * @return array
     */
    public function getCommonCss(): array
    {
        return $this->commonCss;
    }

    /**
     * Returns the CSS files that should render on just the email.
     *
     * @return array
     */
    public function getEmailCss(): array
    {
        return $this->emailCss;
    }
}
