<?php

namespace Ryvon\EventLog\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.1', '<')) {
            $this->addDate($setup);
        }
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $this->addSection($setup);
        }
        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            $this->addRenderer($setup);
        }
        if (version_compare($context->getVersion(), '0.0.4', '<')) {
            $this->removeRendererAndRenameSection($setup);
        }
        if (version_compare($context->getVersion(), '0.0.5', '<')) {
            $this->changeDigestDateStructure($setup);
        }
        if (version_compare($context->getVersion(), '0.0.6', '<')) {
            $this->addDigestKey($setup);
        }
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function addDate($installer)
    {
        $installer->startSetup();

        $installer->getConnection()->addColumn(
            $installer->getTable('event_log_digest'),
            'digest_date',
            [
                'type' => Table::TYPE_DATE,
                'nullable' => false,
                'comment' => 'Digest Date'
            ]
        );

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function addSection($installer)
    {
        $installer->startSetup();

        $installer->getConnection()->addColumn(
            $installer->getTable('event_log_entry'),
            'entry_section',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'nullable' => false,
                'comment' => 'Entry Section'
            ]
        );

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function addRenderer($installer)
    {
        $installer->startSetup();

        $installer->getConnection()->addColumn(
            $installer->getTable('event_log_entry'),
            'entry_renderer',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'nullable' => false,
                'comment' => 'Entry Renderer'
            ]
        );

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function removeRendererAndRenameSection($installer)
    {
        $installer->startSetup();

        $table = $installer->getTable('event_log_entry');

        $installer->getConnection()->dropColumn($table, 'entry_renderer');
        $installer->getConnection()->changeColumn($table, 'entry_section', 'entry_group', [
            'type' => Table::TYPE_TEXT,
            'length' => 32,
            'nullable' => false,
            'comment' => 'Entry Group'
        ]);

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function changeDigestDateStructure($installer)
    {
        $installer->startSetup();

        $table = $installer->getTable('event_log_digest');

        $installer->getConnection()->dropColumn($table, 'digest_date');
        $installer->getConnection()->addColumn($table, 'started_at', [
            'type' => Table::TYPE_TIMESTAMP,
            'nullable' => false,
            'comment' => 'Started At'
        ]);

        $installer->getConnection()->addColumn($table, 'finished_at', [
            'type' => Table::TYPE_TIMESTAMP,
            'nullable' => true,
            'comment' => 'Finished At'
        ]);

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     */
    private function addDigestKey($installer)
    {
        $installer->startSetup();

        $installer->getConnection()->addColumn(
            $installer->getTable('event_log_digest'),
            'digest_key',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 64,
                'nullable' => true,
                'comment' => 'Digest Key'
            ]
        );

        $installer->endSetup();
    }

}
