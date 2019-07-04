<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeTo190
 *
 * Add table for Custom Fields and Custom Fields Conditions
 */
class UpgradeTo190
{
    public function execute(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable('amasty_feed_field')
        )->addColumn(
            'feed_field_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Custom Field Id'
        )->addColumn(
            'code',
            Table::TYPE_TEXT,
            255,
            [],
            'Code'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            [],
            'Name'
        )->addIndex(
            $setup->getIdxName(
                'amasty_feed_field',
                ['code'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['code'],
            ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
        );

        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable(
            $setup->getTable('amasty_feed_field_conditions')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Condition Id'
        )->addColumn(
            'feed_field_id',
            Table::TYPE_INTEGER,
            null,
            ['nullable' => false],
            'Custom Field Id'
        )->addColumn(
            'conditions_serialized',
            Table::TYPE_TEXT,
            '2M',
            [],
            'Conditions'
        )->addColumn(
            'result_serialized',
            Table::TYPE_TEXT,
            '1M',
            [],
            'Result'
        );

        $setup->getConnection()->createTable($table);
    }
}
