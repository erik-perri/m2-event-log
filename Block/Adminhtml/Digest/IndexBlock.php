<?php

namespace Ryvon\EventLog\Block\Adminhtml\Digest;

use Ryvon\EventLog\Block\Adminhtml\TemplateBlock;
use Ryvon\EventLog\Helper\DigestRequestHelper;
use Ryvon\EventLog\Helper\Group\GroupInterface;
use Ryvon\EventLog\Helper\GroupBuilder;
use Ryvon\EventLog\Model\Digest;
use Magento\Backend\Block\Template;

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
    )
    {
        parent::__construct($context, $data);

        $this->digestRequestHelper = $digestRequestHelper;
        $this->groupBuilder = $groupBuilder;
    }

    /**
     * @param Digest $digest
     * @return IndexBlock
     */
    public function setCurrentDigest(Digest $digest): IndexBlock
    {
        $this->currentDigest = $digest;
        return $this;
    }

    /**
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
     * @param Digest $digest
     * @return GroupInterface[]
     */
    public function buildGroups(Digest $digest): array
    {
        return $this->groupBuilder->buildGroups($digest);
    }

    /**
     * @return bool
     */
    public function isSingleStoreMode(): bool
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}
