<?php

namespace Ryvon\EventLog\Helper;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Model\StoreManagerInterface;
use Ryvon\EventLog\Block\Adminhtml\Digest\IndexBlock;
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
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

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
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param DateRangeBuilder $dateRangeBuilder
     * @param EmailEmogrifier $emailEmogrifier
     * @param LayoutInterface $layout
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        EntryRepository $entryRepository,
        DigestSummarizer $digestSummarizer,
        Config $config,
        StoreManagerInterface $storeManager,
        DateRangeBuilder $dateRangeBuilder,
        EmailEmogrifier $emailEmogrifier,
        LayoutInterface $layout
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->entryRepository = $entryRepository;
        $this->digestSummarizer = $digestSummarizer;
        $this->config = $config;
        $this->storeManager = $storeManager;
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

        // DOMDocument seems to not handle multiple root elements well.  We wrap it in a div just in case.
        $emailHtml = '<div>' . $block->toHtml() . '</div>';
        $emailHtml = $this->updateEmailLinks($emailHtml);
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
     * @param string $content
     * @return string
     */
    private function updateEmailLinks($content): string
    {
        if (!$content) {
            return $content;
        }

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
            $includeLinkContent = true;
            $classAttribute = $link->getAttribute('class');
            if ($classAttribute) {
                $classes = explode(' ', $classAttribute);
                // The text in buttons and icons is not useful after removing the link.
                $includeLinkContent = !in_array('icon', $classes, true) && !in_array('btn', $classes, true);
            }

            if ($includeLinkContent) {
                $link->parentNode->replaceChild(
                    new \DOMText($link->textContent),
                    $link
                );
            } else {
                $link->parentNode->removeChild($link);
            }
        }

        $content = $dom->saveHTML();

        libxml_clear_errors();
        libxml_use_internal_errors($previousUseInternalErrors);

        return $content;
    }
}
