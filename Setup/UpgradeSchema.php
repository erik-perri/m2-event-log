<?php

namespace Ryvon\EventLog\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Upgrade schema for Magento <= 2.2.
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Checks the version and applies any upgrades needed.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
    }
}
