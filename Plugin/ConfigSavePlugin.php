<?php

namespace Ryvon\EventLog\Plugin;

use Magento\Backend\Model\Auth\Session;
use Magento\Config\Model\Config;
use Magento\Framework\Event\ManagerInterface;
use Ryvon\EventLog\Helper\StoreViewFinder;

class ConfigSavePlugin
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * @var StoreViewFinder
     */
    private $storeViewFinder;

    /**
     * @param ManagerInterface $eventManager
     * @param Session $authSession
     * @param StoreViewFinder $storeViewFinder
     */
    public function __construct(
        ManagerInterface $eventManager,
        Session $authSession,
        StoreViewFinder $storeViewFinder
    )
    {
        $this->eventManager = $eventManager;
        $this->authSession = $authSession;
        $this->storeViewFinder = $storeViewFinder;
    }

    /**
     * @param Config $subject
     * @param $result
     * @return mixed
     */
    public function afterSave(
        /** @noinspection PhpUnusedParameterInspection */
        Config $subject,
        $result
    )
    {
        if ($this->authSession->getUser()) {
            $this->eventManager->dispatch('event_log_info', [
                'group' => 'admin',
                'message' => 'Configuration section {config-section} modified.',
                'context' => [
                    'store-view' => $this->storeViewFinder->getActiveStoreView(),
                    'config-section' => $subject->getData('section'),
                ],
            ]);
        }

        return $result;
    }
}
