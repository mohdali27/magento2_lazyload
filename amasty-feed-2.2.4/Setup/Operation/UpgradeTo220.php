<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup\Operation;

use Amasty\Feed\Api\Data\ScheduleInterface;
use Amasty\Feed\Model\ResourceModel\Category;
use Amasty\Feed\Model\ResourceModel\Feed;
use Amasty\Feed\Model\ResourceModel\Schedule;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeTo220
{
    /**
     * @param SchemaSetupInterface $setup
     */
    public function execute(SchemaSetupInterface $setup)
    {
        $this->addScheduleTable($setup);
        $this->addFlagTaxonomyColumn($setup);
        $this->addAttributesToGeneratedColumn($setup);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addScheduleTable(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();

        $table = $connection->newTable(
            $setup->getTable(Schedule::TABLE)
        )->addColumn(
            ScheduleInterface::ID,
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'ID'
        )->addColumn(
            ScheduleInterface::CRON_TIME,
            Table::TYPE_INTEGER,
            null,
            [],
            'Cron Time Execution'
        )->addColumn(
            ScheduleInterface::CRON_DAY,
            Table::TYPE_INTEGER,
            null,
            [],
            'Cron Day Execution'
        )->addColumn(
            ScheduleInterface::FEED_ID,
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Feed Id'
        )->addForeignKey(
            $setup->getFkName(
                Schedule::TABLE,
                ScheduleInterface::FEED_ID,
                Feed::TABLE_NAME,
                Feed::ID
            ),
            ScheduleInterface::FEED_ID,
            $setup->getTable(Feed::TABLE_NAME),
            Feed::ID,
            Table::ACTION_CASCADE
        )->setComment(
            'Cron Schedule Execution'
        );

        $connection->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addFlagTaxonomyColumn(SchemaSetupInterface $setup)
    {
        $table = $setup->getTable(Category::TABLE_NAME);
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'use_taxonomy',
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => false,
                'length' => null,
                'comment' => 'Flag to use taxonomy'
            ]
        );

        $connection->addColumn(
            $table,
            'taxonomy_source',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'length' => 25,
                'comment' => 'Source for taxonomy '
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addAttributesToGeneratedColumn($setup)
    {
        $table = $setup->getTable(Feed::TABLE_NAME);
        $connection = $setup->getConnection();

        $connection->addColumn(
            $table,
            'products_amount',
            [
                'type' => Table::TYPE_INTEGER,
                'nullable' => false,
                'length' => null,
                'comment' => 'Number of products'
            ]
        );

        $connection->addColumn(
            $table,
            'generation_type',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => false,
                'length' => 255,
                'comment' => 'Used generation type'
            ]
        );

        $connection->addColumn(
            $table,
            'status',
            [
                'type' => Table::TYPE_SMALLINT,
                'nullable' => false,
                'length' => null,
                'comment' => 'Last feed generation status'
            ]
        );
    }
}
