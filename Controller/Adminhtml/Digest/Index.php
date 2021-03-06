<?php

namespace Ryvon\EventLog\Controller\Adminhtml\Digest;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Event log digest index controller.
 */
class Index extends Action
{
    /**
     * Require event log index acl permissions.
     */
    const ADMIN_RESOURCE = 'Ryvon_EventLog::index';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Action\Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Executes the action.
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $page = $this->resultPageFactory->create();

        $title = $page->getConfig()->getTitle();
        if ($title->get()) {
            $title->set(__($title->get()));
        }

        return $page;
    }
}
