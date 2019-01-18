<?php

namespace Ryvon\EventLog\Plugin;

use Magento\Backend\Model\Auth\Session;
use Magento\Config\Model\Config;
use Magento\Framework\Event\ManagerInterface;
use Ryvon\EventLog\Helper\Group\AdminGroup;

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
     * @param ManagerInterface $eventManager
     * @param Session $authSession
     */
    public function __construct(ManagerInterface $eventManager, Session $authSession)
    {
        $this->eventManager = $eventManager;
        $this->authSession = $authSession;
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
                'group' => AdminGroup::GROUP_ID,
                'message' => 'Configuration section {config-section} modified.',
                'context' => [
                    'config-section' => $subject->getData('section'),
                ],
            ]);
        }

        return $result;
    }
}
