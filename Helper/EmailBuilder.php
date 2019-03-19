<?php

namespace Ryvon\EventLog\Helper;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Ryvon\EventLog\Model\Config;
use Ryvon\EventLog\Model\Digest;
use Ryvon\EventLog\Model\EntryRepository;

class EmailBuilder
{
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var EntryRepository
     */
    private $entryRepository;

    /**
     * @var DigestSummarizer
     */
    private $digestSummarizer;

    /**
     * @var DigestRenderer
     */
    private $digestRenderer;

    /**
     * @var DigestRequestHelper
     */
    private $digestRequestHelper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * @var DateRangeBuilder
     */
    private $dateRangeBuilder;

    /**
     * @param TransportBuilder $transportBuilder
     * @param EntryRepository $entryRepository
     * @param DigestSummarizer $digestSummarizer
     * @param DigestRenderer $digestRenderer
     * @param DigestRequestHelper $digestRequestHelper
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param DeploymentConfig $deploymentConfig
     * @param DateRangeBuilder $dateRangeBuilder
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        EntryRepository $entryRepository,
        DigestSummarizer $digestSummarizer,
        DigestRenderer $digestRenderer,
        DigestRequestHelper $digestRequestHelper,
        Config $config,
        StoreManagerInterface $storeManager,
        DeploymentConfig $deploymentConfig,
        DateRangeBuilder $dateRangeBuilder
    )
    {
        $this->transportBuilder = $transportBuilder;
        $this->entryRepository = $entryRepository;
        $this->digestSummarizer = $digestSummarizer;
        $this->digestRenderer = $digestRenderer;
        $this->digestRequestHelper = $digestRequestHelper;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->deploymentConfig = $deploymentConfig;
        $this->dateRangeBuilder = $dateRangeBuilder;
    }

    /**
     * @param Digest $digest
     * @return TransportBuilder
     */
    public function createDigestEmail(Digest $digest): TransportBuilder
    {
        $entries = $this->entryRepository->findInDigest($digest);
        $summary = $this->digestSummarizer->summarize($entries);

        $subject = sprintf(
            'Event digest (%s) for %s, %s',
            $this->digestSummarizer->getSummaryMessage($summary, true),
            strip_tags($this->dateRangeBuilder->buildDateRange($digest)),
            strip_tags($this->dateRangeBuilder->buildTimeRange($digest))
        );

        $emailData = $this->digestRenderer->renderEntries($entries);
        if ($emailData) {
            $emailData = $this->updateEmailUrls($digest, $emailData);
        } else {
            $emailData = $this->digestRenderer->renderNoEntries();
        }

        $builder = $this->transportBuilder
            ->setTemplateIdentifier('event_log_digest_email_template')
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            ])
            ->setTemplateVars([
                'subject' => $subject,
                'storeUrl' => $this->getStoreUrl(),
                'includeLinks' => $this->config->getIncludeLinksInEmail(),
                'digestUrl' => $this->digestRequestHelper->getDigestUrl(
                    $digest,
                    $this->config->getBypassUrlKey() ? [
                        '_source' => $digest->getDigestKey(), // For the other links in the email this is added in updateEmailUrls
                    ] : []),
                'data' => $emailData,
            ])
            ->setFrom($this->config->getEmailIdentity());

        $setToHeader = false;
        foreach ($this->config->getRecipients() as $address) {
            if (!$setToHeader) {
                $builder->addTo($address);
                $setToHeader = true;
            } else {
                $builder->addBcc($address);
            }
        }

        return $builder;
    }

    /**
     * @param Digest $digest
     * @param string $content
     * @return string
     */
    protected function updateEmailUrls(Digest $digest, $content): string
    {
        if (!$content) {
            return $content;
        }

        $includeLinks = $this->config->getIncludeLinksInEmail();
        $bypassUrlKey = $this->config->getBypassUrlKey();

        $adminPath = sprintf('/%s/', $this->deploymentConfig->get('backend/frontName') ?: 'admin');

        $previousUseInternalErrors = libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        if (defined('LIBXML_HTML_NOIMPLIED') && defined('LIBXML_HTML_NODEFDTD')) {
            $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        } else {
            $dom->loadHTML($content);
        }

        $path = new \DOMXPath($dom);
        $links = $path->query('//a');
        foreach ($links as $link) {
            /** @var \DOMElement $link */

            if (!$includeLinks) {
                $link->parentNode->replaceChild(
                    new \DOMText($link->textContent),
                    $link
                );
                continue;
            }

            if (!$bypassUrlKey) {
                continue;
            }

            $href = $link->getAttribute('href');

            if (strpos($href, $adminPath) !== false && strpos($href, '/key/') !== false) {
                $replacement = sprintf('/_source/%s/key/', $digest->getDigestKey());
                $link->setAttribute('href', str_replace('/key/', $replacement, $href));
            }

            $link->setAttribute('rel', 'nofollow noindex noopener noreferrer');
        }

        $content = $dom->saveHTML();

        libxml_clear_errors();
        libxml_use_internal_errors($previousUseInternalErrors);

        return $content;
    }

    /**
     * @param Digest $digest
     */
    public function debugEmailContents(Digest $digest)
    {
        $builder = $this->createDigestEmail($digest);
        $message = $builder->getTransport()->getMessage();

        echo '<pre>'
            . 'To: ' . implode(', ', $this->config->getRecipients()) . '<br/>'
            . 'Subject: ' . $message->getSubject() . '<br/>'
            . 'Body:' . '<br/>'
            . '</pre>';
        exit($message->getBody()->getRawContent());
    }

    /**
     * @return string
     */
    protected function getStoreUrl()
    {
        try {
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->storeManager->getStore();
            return $store->getBaseUrl();
        } catch (NoSuchEntityException $e) {
            return '/';
        }
    }
}
