<?php

namespace Ryvon\EventLog\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\View\LayoutInterface;
use Ryvon\EventLog\Model\Entry;

class DigestRenderer
{
    /**
     * @var GroupSorter
     */
    private $groupSorter;

    /**
     * @var GroupFinder
     */
    private $groupFinder;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @param GroupSorter $groupSorter
     * @param GroupFinder $groupFinder
     * @param LayoutInterface $layout
     */
    public function __construct(
        GroupSorter $groupSorter,
        GroupFinder $groupFinder,
        LayoutInterface $layout
    )
    {
        $this->groupSorter = $groupSorter;
        $this->groupFinder = $groupFinder;
        $this->layout = $layout;
    }

    /**
     * @param Entry[] $entries
     * @return string
     */
    public function renderEntries($entries): string
    {
        $groups = $this->groupSorter->groupEntries($entries);
        $return = [];

        foreach ($groups as $groupId => $groupEntries) {
            $group = $this->groupFinder->findGroup($groupId);

            // If the group doesn't exist it is likely handled by a plugin that is no longer installed,
            // we create a missing group handler to render it like a log.
            if (!$group) {
                $group = $this->groupFinder->addMissingGroup($groupId);
            }

            if ($group instanceof Group\AbstractLinksGroup) {
                $group->initialize();
            }

            $return[] = $group->setEntries($groupEntries)->render();
        }

        // Remove empty values
        $return = array_filter($return);

        return implode('', $return);
    }

    /**
     * @return string
     */
    public function renderNoEntries(): string
    {
        /** @var \Magento\Backend\Block\Template $block */
        $block = $this->layout->createBlock(\Magento\Backend\Block\Template::class);
        $block->setData('area', Area::AREA_ADMINHTML);
        return $block->setTemplate('Ryvon_EventLog::no-entries.phtml')->toHtml();
    }

    /**
     * @param string $storeUrl
     * @param string $digestUrl
     * @return string
     */
    public function renderHeader(string $storeUrl, string $digestUrl): string
    {
        /** @var \Magento\Backend\Block\Template $block */
        $block = $this->layout->createBlock(\Magento\Backend\Block\Template::class);
        $block->setData('area', Area::AREA_ADMINHTML);
        return $block->setTemplate('Ryvon_EventLog::email-header.phtml')
            ->setData('store-url', $storeUrl)
            ->setData('digest-url', $digestUrl)
            ->toHtml();
    }
}
