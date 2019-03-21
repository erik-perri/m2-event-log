<?php

namespace Ryvon\EventLog\Block\Adminhtml\Digest;

use Ryvon\EventLog\Block\Adminhtml\TemplateBlock;
use Ryvon\EventLog\Helper\DigestRequestHelper;
use Ryvon\EventLog\Helper\Group\GroupInterface;
use Ryvon\EventLog\Helper\GroupBuilder;
use Ryvon\EventLog\Model\Digest;
use Magento\Backend\Block\Template;

/**
 * Block class for the digest index for both the administrator and email.
 */
class IndexBlock extends TemplateBlock
{
    /**
     * @var DigestRequestHelper
     */
    private $digestRequestHelper;

    /**
     * @var GroupBuilder
     */
    private $groupBuilder;

    /**
     * @var Digest
     */
    private $currentDigest;

    /**
     * @param DigestRequestHelper $digestRequestHelper
     * @param GroupBuilder $groupBuilder
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        DigestRequestHelper $digestRequestHelper,
        GroupBuilder $groupBuilder,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->digestRequestHelper = $digestRequestHelper;
        $this->groupBuilder = $groupBuilder;
    }

    /**
     * Retrieves the current digest from the current request, using the newest if none is specified.
     *
     * @return Digest|null
     */
    public function getCurrentDigest()
    {
        if ($this->currentDigest === null) {
            $this->currentDigest = $this->digestRequestHelper->getCurrentDigest($this->getRequest());
        }

        return $this->currentDigest;
    }

    /**
     * Set the current digest. This is used in the cron/CLI email sending since no request is available.
     *
     * @param Digest $digest
     * @return IndexBlock
     */
    public function setCurrentDigest(Digest $digest): IndexBlock
    {
        $this->currentDigest = $digest;
        return $this;
    }

    /**
     * Retrieves the group classes and groups the entries in them.
     *
     * @param Digest $digest
     * @return GroupInterface[]
     */
    public function buildGroups(Digest $digest): array
    {
        return $this->groupBuilder->buildGroups($digest);
    }

    /**
     * Retrieves the setting for whether or not the store is in single-store mode.
     *
     * If the store is in single-store mode there is no need to render the store view column.
     *
     * @return bool
     */
    public function isSingleStoreMode(): bool
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}
