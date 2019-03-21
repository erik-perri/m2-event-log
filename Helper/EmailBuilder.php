<?php

namespace Ryvon\EventLog\Helper;

use Ryvon\EventLog\Block\Adminhtml\Digest\IndexBlock;
use Ryvon\EventLog\Model\Config;
use Ryvon\EventLog\Model\Digest;
use Ryvon\EventLog\Model\EntryRepository;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;

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
     * @var EmailEmogrifier
     */
    private $emailEmogrifier;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @param TransportBuilder $transportBuilder
     * @param EntryRepository $entryRepository
     * @param DigestSummarizer $digestSummarizer
     * @param DigestRequestHelper $digestRequestHelper
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param DeploymentConfig $deploymentConfig
     * @param DateRangeBuilder $dateRangeBuilder
     * @param EmailEmogrifier $emailEmogrifier
     * @param LayoutInterface $layout
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        EntryRepository $entryRepository,
        DigestSummarizer $digestSummarizer,
        DigestRequestHelper $digestRequestHelper,
        Config $config,
        StoreManagerInterface $storeManager,
        DeploymentConfig $deploymentConfig,
        DateRangeBuilder $dateRangeBuilder,
        EmailEmogrifier $emailEmogrifier,
        LayoutInterface $layout
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->entryRepository = $entryRepository;
        $this->digestSummarizer = $digestSummarizer;
        $this->digestRequestHelper = $digestRequestHelper;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->deploymentConfig = $deploymentConfig;
        $this->dateRangeBuilder = $dateRangeBuilder;
        $this->emailEmogrifier = $emailEmogrifier;
        $this->layout = $layout;
    }

    /**
     * @param Digest $digest
     * @return TransportBuilder
     */
    public function createDigestEmail(Digest $digest): TransportBuilder
    {
        $entries = $this->entryRepository->findInDigest($digest);
        $summary = $this->digestSummarizer->summarize(
            $entries->getUnfilteredItems(),
            true
        );

        $subject = sprintf(
            'Event digest (%s) for %s, %s',
            $this->digestSummarizer->getSummaryMessage($summary, true),
            strip_tags($this->dateRangeBuilder->buildDateRange($digest->getStartedAt(), $digest->getFinishedAt())),
            strip_tags($this->dateRangeBuilder->buildTimeRange($digest->getStartedAt(), $digest->getFinishedAt()))
        );

        /** @var IndexBlock $block */
        $block = $this->layout->createBlock(IndexBlock::class);
        $block
            ->setTemplate('Ryvon_EventLog::index.phtml')
            ->setCurrentDigest($digest)
            ->setData('email', true)
            // We need to set the area on the block or Magento will set it to crontab and fail to find the templates \
            // when running this code through the cron.
            ->setData('area', \Magento\Framework\App\Area::AREA_ADMINHTML);

        $emailHtml = $block->toHtml();

        if ($this->config->getIncludeLinksInEmail()) {
            /** @var \Magento\Backend\Block\Template $headerBlock */
            $headerBlock = $this->layout->createBlock(\Magento\Backend\Block\Template::class);
            $headerBlock->setData('area', \Magento\Framework\App\Area::AREA_ADMINHTML);
            $headerHtml = $headerBlock->setTemplate('Ryvon_EventLog::email-header.phtml')
                ->setData('store-url', $this->getStoreUrl())
                ->setData('digest-url', $this->digestRequestHelper->getDigestUrl($digest))
                ->toHtml();
            // We need to wrap both in a container or \DOMDocument will put emailHtml inside headerHtml's div.
            $emailHtml = '<div>' . $headerHtml . $emailHtml . '</div>';
        }

        $emailHtml = $this->updateEmailLinks($digest, $emailHtml);
        $emailHtml = $this->emailEmogrifier->emogrify($emailHtml);

        $builder = $this->transportBuilder
            ->setTemplateIdentifier('event_log_digest_email_template')
            ->setTemplateOptions([
                'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            ])
            ->setTemplateVars([
                'subject' => $subject,
                'data' => $emailHtml,
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
     * @return string
     */
    private function getStoreUrl()
    {
        try {
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->storeManager->getStore();
            return $store->getBaseUrl();
        } catch (NoSuchEntityException $e) {
            return '/';
        }
    }

    /**
     * @param Digest $digest
     * @param string $content
     * @return string
     */
    private function updateEmailLinks(Digest $digest, $content): string
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
}
