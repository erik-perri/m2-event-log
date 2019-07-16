<?php

namespace Ryvon\EventLog\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Ryvon\EventLog\Helper\CssHelper;

/**
 * Block to include the provided CSS files.
 */
class CssBlock extends Template
{
    /**
     * @var CssHelper
     */
    private $cssHelper;

    /**
     * @param Context $context
     * @param CssHelper $cssHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CssHelper $cssHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->cssHelper = $cssHelper;
    }

    /**
     * Returns the CSS file that should render on both the admin and email.
     *
     * @return array
     */
    public function getCommonCss(): array
    {
        return $this->cssHelper->getCommonCss();
    }
}
