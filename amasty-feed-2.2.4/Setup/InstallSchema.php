<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_feed_entity')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Rule Id'
        )->addColumn(
            'name',
            Table::TYPE_TEXT,
            255,
            [],
            'Name'
        )->addColumn(
            'filename',
            Table::TYPE_TEXT,
            255,
            [],
            'File Name'
        )->addColumn(
            'feed_type',
            Table::TYPE_TEXT,
            255,
            [],
            'Feed Type'
        )->addColumn(
            'is_active',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Is Active'
        )->addColumn(
            'store_id',
            Table::TYPE_SMALLINT,
            null,
            ['unsigned' => true, 'nullable' => true, 'default' => '0'],
            'Store ID'
        )->addColumn(
            'execute_mode',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => 'manual'],
            'Execute Mode'
        )->addColumn(
            'cron_time',
            Table::TYPE_TEXT,
            255,
            ['nullable' => true],
            'Cron Time'
        )->addColumn(
            'csv_column_name',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Csv Column Name'
        )->addColumn(
            'csv_header',
            Table::TYPE_TEXT,
            '64k',
            [],
            'Csv Header'
        )->addColumn(
            'csv_enclosure',
            Table::TYPE_TEXT,
            255,
            [],
            'Csv Enclosure'
        )->addColumn(
            'csv_delimiter',
            Table::TYPE_TEXT,
            255,
            [],
            'Csv Delimiter'
        )->addColumn(
            'format_price_currency',
            Table::TYPE_TEXT,
            255,
            [],
            'Format Price Currency'
        )->addColumn(
            'csv_field',
            Table::TYPE_TEXT,
            '128k',
            [],
            'Csv Field'
        )->addColumn(
            'xml_header',
            Table::TYPE_TEXT,
            '128k',
            [],
            'Xml Header'
        )->addColumn(
            'xml_item',
            Table::TYPE_TEXT,
            255,
            [],
            'Xml Item'
        )->addColumn(
            'xml_content',
            Table::TYPE_TEXT,
            '128k',
            [],
            'Xml Content'
        )->addColumn(
            'xml_footer',
            Table::TYPE_TEXT,
            '128k',
            [],
            'Xml Footer'
        )->addColumn(
            'format_price_currency_show',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '1'],
            'Format Price Currency Show'
        )->addColumn(
            'format_price_decimals',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => 'two'],
            'Format Price Decimals'
        )->addColumn(
            'format_price_decimal_point',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => 'dot'],
            'Format Price Decimal Point'
        )->addColumn(
            'format_price_thousands_separator',
            Table::TYPE_TEXT,
            255,
            ['nullable' => false, 'default' => 'comma'],
            'Format Price Thousands Separator'
        )->addColumn(
            'format_date',
            Table::TYPE_TEXT,
            255,
            [],
            'Format Date'
        )->addColumn(
            'conditions_serialized',
            Table::TYPE_TEXT,
            '128k',
            [],
            'Conditions Serialized'
        )->addColumn(
            'generated_at',
            Table::TYPE_TIMESTAMP,
            null,
            [],
            'Generated At'
        )->addColumn(
            'delivery_enabled',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Delivery Enabled'
        )->addColumn(
            'delivery_host',
            Table::TYPE_TEXT,
            255,
            [],
            'Delivery Host'
        )->addColumn(
            'delivery_type',
            Table::TYPE_TEXT,
            255,
            [],
            'Delivery Type'
        )->addColumn(
            'delivery_user',
            Table::TYPE_TEXT,
            255,
            [],
            'Delivery User'
        )->addColumn(
            'delivery_password',
            Table::TYPE_TEXT,
            255,
            [],
            'Delivery Password'
        )->addColumn(
            'delivery_path',
            Table::TYPE_TEXT,
            255,
            [],
            'Delivery Path'
        )->addColumn(
            'delivery_passive_mode',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Delivery Passive Mode'
        )->addColumn(
            'utm_source',
            Table::TYPE_TEXT,
            255,
            [],
            'Utm Source'
        )->addColumn(
            'utm_medium',
            Table::TYPE_TEXT,
            255,
            [],
            'Utm Medium'
        )->addColumn(
            'utm_term',
            Table::TYPE_TEXT,
            255,
            [],
            'Utm Term'
        )->addColumn(
            'utm_content',
            Table::TYPE_TEXT,
            255,
            [],
            'Utm Content'
        )->addColumn(
            'utm_campaign',
            Table::TYPE_TEXT,
            255,
            [],
            'Utm Campaign'
        )->addColumn(
            'is_template',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Is Template'
        )->addIndex(
            $installer->getIdxName('amasty_feed_entity', ['store_id']),
            ['store_id']
        );

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_feed_category')
        )->addColumn(
            'feed_category_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Feed Category Id'
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
        );

        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()->newTable(
            $installer->getTable('amasty_feed_category_mapping')
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Rule Id'
        )->addColumn(
            'feed_category_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Feed Category ID'
        )->addColumn(
            'category_id',
            Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
            'Category ID'
        )->addColumn(
            'variable',
            Table::TYPE_TEXT,
            255,
            [],
            'Variable'
        )->addIndex(
            $installer->getIdxName('amasty_feed_category', ['feed_category_id']),
            ['feed_category_id']
        )->addForeignKey(
            $installer->getFkName(
                'amasty_feed_category',
                'feed_category_id',
                'feed_category',
                'feed_category_id'
            ),
            'feed_category_id',
            $installer->getTable('amasty_feed_category'),
            'feed_category_id',
            Table::ACTION_CASCADE
        )->addIndex(
            $installer->getIdxName('catalog_category_entity', ['category_id']),
            ['category_id']
        );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
