<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeTo170
{
    /**
     * @var \Amasty\Feed\Model\Indexer\Feed\IndexBuilder
     */
    private $feedBuilder;

    public function __construct(\Amasty\Feed\Model\Indexer\Feed\IndexBuilder $feedBuilder)
    {
        $this->feedBuilder = $feedBuilder;
    }

    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->createTable($setup);
        $this->feedBuilder->reindexFull();
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    private function createTable(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable('amasty_feed_valid_products')
        )->addColumn(
            'entity_id',
            Table::TYPE_BIGINT,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity Id'
        )->addColumn(
            'feed_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Feed ID'
        )->addColumn(
            'valid_product_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Valid products for conditions'
        )->addIndex(
            $setup->getIdxName(
                'amasty_feed_valid_products',
                ['valid_product_id']
            ),
            ['valid_product_id']
        )->addForeignKey(
            $setup->getFkName(
                'amasty_feed_valid_products',
                'feed_id',
                'amasty_feed_entity',
                'entity_id'
            ),
            'feed_id',
            $setup->getTable('amasty_feed_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        );

        $setup->getConnection()->createTable($table);
    }
}
