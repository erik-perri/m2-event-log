<?php

namespace Ryvon\EventLog\Block\Adminhtml\Digest;

use Magento\Backend\Block\Template;
use Ryvon\EventLog\Helper\DigestRenderer;
use Ryvon\EventLog\Helper\DigestRequestHelper;
use Ryvon\EventLog\Model\Digest;
use Ryvon\EventLog\Model\Entry;
use Ryvon\EventLog\Model\EntryRepository;

class EntryListBlock extends Template
{
    /**
     * @var EntryRepository
     */
    private $entryRepository;

    /**
     * @var DigestRenderer
     */
    private $digestRenderer;

    /**
     * @var DigestRequestHelper
     */
    private $digestRequestHelper;

    /**
     * @param EntryRepository $entryRepository
     * @param DigestRenderer $digestRenderer
     * @param DigestRequestHelper $digestRequestHelper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        EntryRepository $entryRepository,
        DigestRenderer $digestRenderer,
        DigestRequestHelper $digestRequestHelper,
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->entryRepository = $entryRepository;
        $this->digestRenderer = $digestRenderer;
        $this->digestRequestHelper = $digestRequestHelper;
    }

    /**
     * @return Digest
     */
    public function getCurrentDigest(): Digest
    {
        return $this->digestRequestHelper->getCurrentDigest($this->getRequest());
    }

    /**
     * @param Digest $digest
     * @return Entry[]
     */
    public function getEntries($digest): array
    {
        if (!$digest) {
            return [];
        }

        return $this->entryRepository->findInDigest($digest);
    }

    /**
     * @return DigestRenderer
     */
    public function getDigestRenderer(): DigestRenderer
    {
        return $this->digestRenderer;
    }
}
