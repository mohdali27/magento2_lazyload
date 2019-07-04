<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup\Operation;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeTo191
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable('amasty_feed_entity');
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'cron_day',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'length' => 255,
                'comment' => 'Cron Day Execution'
            ]
        );
    }
}
