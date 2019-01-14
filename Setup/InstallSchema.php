<?php

namespace Ryvon\EventLog\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
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
            ->addColumn('digest_id',
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
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT,
                ],
                'Created At'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT_UPDATE,
                ],
                'Updated At'
            )
            ->setComment('Digest Table');

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }

    /**
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
            ->addColumn('entry_id',
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
            ->addColumn('digest_id',
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
