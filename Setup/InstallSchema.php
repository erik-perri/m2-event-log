<?php

namespace Ryvon\EventLog\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Install schema for Magento <= 2.2.
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * Setup the database tables.
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->setupDigestTable($setup);
        $this->setupEntryTable($setup);
    }

    /**
     * Setup the digest table.
     *
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     */
    private function setupDigestTable($installer)
    {
        $installer->startSetup();
        if ($installer->tableExists('event_log_digest')) {
            $installer->endSetup();
            return;
        }

        $table = $installer->getConnection()->newTable($installer->getTable('event_log_digest'))
            ->addColumn(
                'digest_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ],
                'Digest ID'
            )
            ->addColumn(
                'digest_key',
                Table::TYPE_TEXT,
                64,
                [
                    'nullable' => true,
                ],
                'Digest Key'
            )
            ->addColumn(
                'started_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                ],
                'Started At'
            )
            ->addColumn(
                'finished_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => true,
                ],
                'Finished At'
            )
            ->setComment('Digest Table');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

    /**
     * Setup the entry table.
     *
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     */
    private function setupEntryTable($installer)
    {
        $installer->startSetup();
        if ($installer->tableExists('event_log_entry')) {
            $installer->endSetup();
            return;
        }

        $table = $installer->getConnection()->newTable($installer->getTable('event_log_entry'))
            ->addColumn(
                'entry_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                ],
                'Entry ID'
            )
            ->addColumn(
                'digest_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'nullable' => false,
                    'unsigned' => true,
                ],
                'Digest ID'
            )
            ->addColumn(
                'entry_level',
                Table::TYPE_TEXT,
                12,
                ['nullable' => false],
                'Entry Level'
            )
            ->addColumn(
                'entry_group',
                Table::TYPE_TEXT,
                32,
                ['nullable' => false],
                'Entry Group'
            )
            ->addColumn(
                'entry_message',
                Table::TYPE_TEXT,
                128,
                ['nullable' => false],
                'Entry Message'
            )
            ->addColumn(
                'entry_context',
                Table::TYPE_TEXT,
                512,
                ['nullable' => false],
                'Entry Context'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT,
                ],
                'Created At'
            )
            ->addForeignKey(
                $installer->getFkName(
                    'event_log_entry',
                    'digest_id',
                    'event_log_digest',
                    'digest_id'
                ),
                'digest_id',
                $installer->getTable('event_log_digest'),
                'digest_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Entry Table');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
