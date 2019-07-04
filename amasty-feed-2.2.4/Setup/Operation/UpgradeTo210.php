<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Amasty\Feed\Model\ResourceModel\Category\Taxonomy;

class UpgradeTo210
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();

        $table = $connection->newTable(
            $setup->getTable(Taxonomy::TABLE_NAME)
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            'category',
            Table::TYPE_TEXT,
            null,
            [],
            'Category'
        )->addColumn(
            'language_code',
            Table::TYPE_TEXT,
            null,
            [],
            'Language Code'
        );

        $setup->getConnection()->createTable($table);
    }
}
