<?php

namespace Ryvon\EventLog\Block\Adminhtml\Digest;

use Magento\Backend\Block\Template;
use Ryvon\EventLog\Helper\DateRangeBuilder;
use Ryvon\EventLog\Helper\DigestRequestHelper;
use Ryvon\EventLog\Model\Digest;
use Ryvon\EventLog\Model\DigestRepository;

class DateBlock extends Template
{
    /**
     * @var DigestRepository
     */
    private $digestRepository;

    /**
     * @var DigestRequestHelper
     */
    private $digestRequestHelper;

    /**
     * @var DateRangeBuilder
     */
    private $dateRangeBuilder;

    /**
     * @param DigestRepository $digestRepository
     * @param DigestRequestHelper $digestRequestHelper
     * @param DateRangeBuilder $dateRangeBuilder
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        DigestRepository $digestRepository,
        DigestRequestHelper $digestRequestHelper,
        DateRangeBuilder $dateRangeBuilder,
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->digestRepository = $digestRepository;
        $this->digestRequestHelper = $digestRequestHelper;
        $this->dateRangeBuilder = $dateRangeBuilder;
    }

    /**
     * @return Digest|null
     */
    public function getCurrentDigest()
    {
        return $this->digestRequestHelper->getCurrentDigest($this->getRequest());
    }

    /**
     * @param Digest $digest
     * @return Digest|null
     */
    public function getPreviousDigest(Digest $digest)
    {
        return $this->digestRepository->findPreviousDigest($digest->getStartedAt());
    }

    /**
     * @param Digest $digest
     * @return Digest|null
     */
    public function getNextDigest(Digest $digest)
    {
        return $this->digestRepository->findNextDigest($digest->getStartedAt());
    }

    /**
     * @param Digest $digest
     * @return string
     */
    public function getDigestUrl(Digest $digest): string
    {
        return $this->digestRequestHelper->getDigestUrl($digest);
    }

    /**
     * @return DateRangeBuilder
     */
    public function getDateRangeBuilder(): DateRangeBuilder
    {
        return $this->dateRangeBuilder;
    }
}
