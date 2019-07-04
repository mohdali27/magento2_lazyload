<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeTo160
{
    /**
     * @param SchemaSetupInterface $setup
     *
     * @throws \Zend_Db_Exception
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_feed_entity');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'exclude_disabled',
            [
                'type'     => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default'  => false,
                'comment'  => 'Exclude Disabled Products'
            ]
        );
        $connection->addColumn(
            $table,
            'exclude_out_of_stock',
            [
                'type'     => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default'  => false,
                'comment'  => 'Exclude Out of Stock Products'
            ]
        );
        $connection->addColumn(
            $table,
            'exclude_not_visible',
            [
                'type'     => Table::TYPE_BOOLEAN,
                'nullable' => false,
                'default'  => false,
                'comment'  => 'Exclude Not Visible Products'
            ]
        );
    }
}
