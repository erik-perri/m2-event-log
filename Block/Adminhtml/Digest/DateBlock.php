<?php

namespace Ryvon\EventLog\Block\Adminhtml\Digest;

use Ryvon\EventLog\Helper\DateRangeBuilder;
use Ryvon\EventLog\Helper\DigestRequestHelper;
use Ryvon\EventLog\Model\Digest;
use Ryvon\EventLog\Model\DigestRepository;
use Magento\Backend\Block\Template;

/**
 * Block class for the date pagination for the administrator.
 */
class DateBlock extends Template
{
    /**
     * @var DateRangeBuilder
     */
    private $dateRangeBuilder;

    /**
     * @var DigestRepository
     */
    private $digestRepository;

    /**
     * @var DigestRequestHelper
     */
    private $digestRequestHelper;

    /**
     * @param DateRangeBuilder $dateRangeBuilder
     * @param DigestRepository $digestRepository
     * @param DigestRequestHelper $digestRequestHelper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        DateRangeBuilder $dateRangeBuilder,
        DigestRepository $digestRepository,
        DigestRequestHelper $digestRequestHelper,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->dateRangeBuilder = $dateRangeBuilder;
        $this->digestRepository = $digestRepository;
        $this->digestRequestHelper = $digestRequestHelper;
    }

    /**
     * Retrieves the current digest from the current request, using the newest if none is specified.
     *
     * @return Digest|null
     */
    public function getCurrentDigest()
    {
        return $this->digestRequestHelper->getCurrentDigest($this->getRequest());
    }

    /**
     * Retrieves the previous digest based on the specified digest.
     *
     * @param Digest $digest
     * @return Digest|null
     */
    public function getPreviousDigest(Digest $digest)
    {
        return $this->digestRepository->findPreviousDigest($digest->getStartedAt());
    }

    /**
     * Retrieves the next digest based on the specified digest.
     *
     * @param Digest $digest
     * @return Digest|null
     */
    public function getNextDigest(Digest $digest)
    {
        return $this->digestRepository->findNextDigest($digest->getStartedAt());
    }

    /**
     * Generates a digest URL for the specified digest.
     *
     * @param Digest $digest
     * @return string
     */
    public function getDigestUrl(Digest $digest): string
    {
        return $this->digestRequestHelper->getDigestUrl($digest);
    }

    /**
     * Helper function to retrieve the date range builder.
     *
     * @return DateRangeBuilder
     */
    public function getDateRangeBuilder(): DateRangeBuilder
    {
        return $this->dateRangeBuilder;
    }
}
