<?php

namespace Ryvon\EventLog\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function getHideDuplicateEntries()
    {
        return $this->scopeConfig->getValue('system/event_log/hide_duplicates', ScopeInterface::SCOPE_WEBSITE) > 0;
    }

    /**
     * @return bool
     */
    public function getEnableDigestEmail()
    {
        return $this->scopeConfig->getValue('system/event_log/enable_digest_email', ScopeInterface::SCOPE_WEBSITE) > 0;
    }

    /**
     * @return bool
     */
    public function getInternalDigestCron()
    {
        return $this->scopeConfig->getValue('system/event_log/internal_digest_cron', ScopeInterface::SCOPE_WEBSITE) > 0;
    }

    /**
     * @return array|null
     */
    public function getRecipients()
    {
        $recipientsValue = $this->scopeConfig->getValue('system/event_log/recipient_email', ScopeInterface::SCOPE_WEBSITE);
        if (!$recipientsValue || strpos($recipientsValue, '@example.com') !== false) {
            return null;
        }
        if (strpos($recipientsValue, ',') !== false) {
            $recipients = array_values(array_filter(array_map('trim', explode(',', $recipientsValue))));
        } else {
            $recipients = [$recipientsValue];
        }

        return $recipients;
    }

    /**
     * @return string
     */
    public function getEmailIdentity()
    {
        return $this->scopeConfig->getValue('system/event_log/sender_email_identity', ScopeInterface::SCOPE_WEBSITE);
    }
}
