<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Setup;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Magestore\InventorySuccess\Setup
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return $this
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $setup->getConnection()->dropTable($setup->getTable('os_warehouse'));
        $setup->getConnection()->dropTable($setup->getTable('os_warehouse_product'));
        $setup->getConnection()->dropTable($setup->getTable('os_warehouse_location'));
        $setup->getConnection()->dropTable($setup->getTable('os_permission'));
        $setup->getConnection()->dropTable($setup->getTable('os_stock_movement'));
        $setup->getConnection()->dropTable($setup->getTable('os_adjuststock'));
        $setup->getConnection()->dropTable($setup->getTable('os_adjuststock_product'));
        $setup->getConnection()->dropTable($setup->getTable('os_stocktaking'));
        $setup->getConnection()->dropTable($setup->getTable('os_stocktaking_product'));
        $setup->getConnection()->dropTable($setup->getTable('os_transferstock'));
        $setup->getConnection()->dropTable($setup->getTable('os_transferstock_product'));
        $setup->getConnection()->dropTable($setup->getTable('os_transferstock_product'));
        $setup->getConnection()->dropTable($setup->getTable('os_transferstock_activity'));
        $setup->getConnection()->dropTable($setup->getTable('os_transferstock_activity_product'));

        $setup->getConnection()->dropTable($setup->getTable('os_warehouse_order_item'));
        $setup->getConnection()->dropTable($setup->getTable('os_warehouse_shipment_item'));
        $setup->getConnection()->dropTable($setup->getTable('os_warehouse_creditmemo_item'));
        $setup->getConnection()->dropTable($setup->getTable('os_ship_product'));

        $setup->getConnection()->dropTable($setup->getTable('os_increment_id'));

        $setup->getConnection()->dropTable($setup->getTable('os_lowstock_notification_rule'));
        $setup->getConnection()->dropTable($setup->getTable('os_lowstock_notification_rule_product'));
        $setup->getConnection()->dropTable($setup->getTable('os_lowstock_notification'));
        $setup->getConnection()->dropTable($setup->getTable('os_lowstock_notification_product'));


        /**
         * create os_warehouse table
         */
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_warehouse'))
            ->addColumn(
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Warehouse Id'
            )->addColumn(
                'warehouse_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Warehouse Name'
            )->addColumn(
                'warehouse_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Warehouse Code'
            )->addColumn(
                'contact_email',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Contact Email'
            )->addColumn(
                'telephone',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                50,
                ['default' => null],
                'Telephone'
            )->addColumn(
                'street',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Street'
            )->addColumn(
                'city',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'City'
            )->addColumn(
                'country_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                3,
                ['default' => null],
                'Country Id'
            )->addColumn(
                'region',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Region'
            )->addColumn(
                'region_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Region Id'
            )->addColumn(
                'postcode',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Postcode'
            )
//             ->addColumn(
//                 'status',
//                 \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
//                 1,
//                 ['nullable' => false, 'default' => 1],
//                 'Status'
//             )
            ->addColumn(
                'is_primary',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['default' => '0'],
                'Is Primary'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addIndex(
                $installer->getIdxName(
                    'os_warehouse',
                    ['warehouse_code'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['warehouse_code'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            );
        $installer->getConnection()->createTable($table);

        /**
         * create os_warehouse_location table
         */
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_warehouse_location'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'location_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Location Id'
            )
            ->addColumn(
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['unsigned' => true, 'nullable' => false],
                'Warehouse Id'
            )->addIndex(
                $installer->getIdxName('os_warehouse_location', ['warehouse_id']),
                ['warehouse_id']
            )->addForeignKey(
                $installer->getFkName(
                    'os_warehouse_location',
                    'warehouse_id',
                    'os_warehouse',
                    'warehouse_id'
                ),
                'warehouse_id',
                $installer->getTable('os_warehouse'),
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);

        /**
         * create os_warehouse_product table
         */
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_warehouse_product'))
            ->addColumn(
                'warehouse_product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Warehouse Product Id'
            )->addColumn(
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Warehouse Id'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Product Id'
            )->addColumn(
                'total_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => 0],
                'Total Qty'
            )->addColumn(
                'qty_to_ship',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => false, 'default' => 0],
                'Qty to Ship'
            )->addColumn(
                'shelf_location',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => ''],
                'Shelf Location'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addIndex(
                $installer->getIdxName('os_warehouse_product', ['warehouse_id']),
                ['warehouse_id']
            )->addIndex(
                $installer->getIdxName('os_warehouse_product', ['product_id']),
                ['product_id']
            )->addIndex(
                $installer->getIdxName(
                    'os_warehouse_product',
                    ['warehouse_id', 'product_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['warehouse_id', 'product_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addForeignKey(
                $installer->getFkName(
                    'os_warehouse_product',
                    'warehouse_id',
                    'os_warehouse',
                    'warehouse_id'
                ),
                'warehouse_id',
                $installer->getTable('os_warehouse'),
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'os_warehouse_product',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);

        /**
         * create os_stock_movement table
         */
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_stock_movement'))
            ->addColumn(
                'stock_movement_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Stock Movement Id'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'product Id'
            )->addColumn(
                'product_sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Product SKU'
            )->addColumn(
                'qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Qty'
            )->addColumn(
                'action_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Action Code'
            )->addColumn(
                'action_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null],
                'Action Id'
            )->addColumn(
                'action_number',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => ''],
                'Action Number'
            )->addColumn(
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => true, 'default' => null],
                'Warehouse Id'
            )
//            ->addColumn(
//                'source_warehouse',
//                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
//                255,
//                ['default' => ''],
//                'Source Warehouse'
//            )->addColumn(
//                'des_warehouse_id',
//                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
//                11,
//                ['nullable' => true, 'default' => null],
//                'Destination Warehouse Id'
//            )->addColumn(
//                'des_warehouse',
//                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
//                255,
//                ['default' => ''],
//                'Destination Warehouse'
//            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addIndex(
                $installer->getIdxName('os_stock_movement', ['product_id']),
                ['product_id']
            )->addIndex(
                $installer->getIdxName('os_stock_movement', ['product_sku']),
                ['product_sku']
            );
        $installer->getConnection()->createTable($table);

        /**
         * create os_adjuststock table
         */
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_adjuststock'))
            ->addColumn(
                'adjuststock_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Adjuststock Id'
            )->addColumn(
                'adjuststock_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Adjuststock Code'
            )
            ->addColumn(
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Warehouse Id'
            )->addColumn(
                'warehouse_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Warehouse Name'
            )->addColumn(
                'warehouse_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Warehouse Code'
            )->addColumn(
                'reason',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Reason'
            )->addColumn(
                'created_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Created By'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['default' => null],
                'Created At'
            )->addColumn(
                'confirmed_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Confirmed By'
            )->addColumn(
                'confirmed_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['default' => null],
                'Confirmed At'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['default' => null],
                'Status'
            )->addIndex(
                $installer->getIdxName(
                    'os_adjuststock_adjuststock_code',
                    ['adjuststock_code'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['adjuststock_code'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addIndex(
                $installer->getIdxName('os_adjuststock', ['warehouse_id']),
                ['warehouse_id']
            )->addForeignKey(
                $installer->getFkName(
                    'os_adjuststock',
                    'warehouse_id',
                    'os_warehouse',
                    'warehouse_id'
                ),
                'warehouse_id',
                $installer->getTable('os_warehouse'),
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);

        /**
         * create  os_adjuststock_product table
         */
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_adjuststock_product'))
            ->addColumn(
                'adjuststock_product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Adjuststock Product Id'
            )
            ->addColumn(
                'adjuststock_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Adjuststock Id'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Product Id'
            )->addColumn(
                'product_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Product Name'
            )->addColumn(
                'product_sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Product SKU'
            )->addColumn(
                'old_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Old Qty'
            )->addColumn(
                'adjust_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Adjust Qty'
            )->addColumn(
                'change_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Change Qty'
            )->addIndex(
                $installer->getIdxName('os_adjuststock_product', ['adjuststock_id']),
                ['adjuststock_id']
            )->addIndex(
                $installer->getIdxName('os_adjuststock_product', ['product_id']),
                ['product_id']
            )->addForeignKey(
                $installer->getFkName(
                    'os_adjuststock_product',
                    'adjuststock_id',
                    'os_adjuststock',
                    'adjuststock_id'
                ),
                'adjuststock_id',
                $installer->getTable('os_adjuststock'),
                'adjuststock_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'os_adjuststock_product',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);

        /**
         * create stocktaking table
         */
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_stocktaking'))
            ->addColumn(
                'stocktaking_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Stocktaking Id'
            )->addColumn(
                'stocktaking_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Stocktaking Code'
            )
            ->addColumn(
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Warehouse Id'
            )->addColumn(
                'warehouse_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Warehouse Name'
            )->addColumn(
                'warehouse_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Warehouse Code'
            )->addColumn(
                'participants',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Participants'
            )->addColumn(
                'stocktake_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['default' => null],
                'Stocktake At'
            )->addColumn(
                'reason',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Reason'
            )->addColumn(
                'created_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Created By'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['default' => null],
                'Created At'
            )->addColumn(
                'verified_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Verified By'
            )->addColumn(
                'verified_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['default' => null],
                'Verified At'
            )->addColumn(
                'confirmed_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Confirmed By'
            )->addColumn(
                'confirmed_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['default' => null],
                'Confirmed At'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['default' => null],
                'Status'
            )->addIndex(
                $installer->getIdxName(
                    'os_stocktaking_stocktaking_code',
                    ['stocktaking_code'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['stocktaking_code'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addIndex(
                $installer->getIdxName('os_stocktaking', ['warehouse_id']),
                ['warehouse_id']
            )->addForeignKey(
                $installer->getFkName(
                    'os_stocktaking',
                    'warehouse_id',
                    'os_warehouse',
                    'warehouse_id'
                ),
                'warehouse_id',
                $installer->getTable('os_warehouse'),
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);

        /**
         * create  os_stocktaking_product table
         */
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_stocktaking_product'))
            ->addColumn(
                'stocktaking_product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Stocktaking Product Id'
            )
            ->addColumn(
                'stocktaking_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Stocktaking Id'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Product Id'
            )->addColumn(
                'product_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Product Name'
            )->addColumn(
                'product_sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Product SKU'
            )->addColumn(
                'old_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Old Qty'
            )->addColumn(
                'stocktaking_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['nullable' => true],
                'Stocktaking Qty'
            )->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['default' => null],
                'Type'
            )->addIndex(
                $installer->getIdxName('os_stocktaking_product', ['stocktaking_id']),
                ['stocktaking_id']
            )->addIndex(
                $installer->getIdxName('os_stocktaking_product', ['product_id']),
                ['product_id']
            )->addForeignKey(
                $installer->getFkName(
                    'os_stocktaking_product',
                    'stocktaking_id',
                    'os_stocktaking',
                    'stocktaking_id'
                ),
                'stocktaking_id',
                $installer->getTable('os_stocktaking'),
                'stocktaking_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'os_stocktaking_product',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);
        /**
         * create os_transferstock table
         */
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_transferstock'))
            ->addColumn(
                'transferstock_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Transfer Stock Id'
            )->addColumn(
                'transferstock_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Transfer Stock Code'
            )
            ->addColumn(
                'source_warehouse_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Soruce Warehouse Id'
            )->addColumn(
                'source_warehouse_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Source Warehouse Code'
            )->addColumn(
                'des_warehouse_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Destination Warehouse Id'
            )->addColumn(
                'des_warehouse_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Destination Warehouse Code'
            )->addColumn(
                'reason',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Reason'
            )->addColumn(
                'created_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Created By'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['default' => null],
                'Created At'
            )->addColumn(
                'external_location',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'External Location'
            )->addColumn(
                'notifier_emails',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Notifier Emails'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Status'
            )->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Transfer Type'
            )->addColumn(
                'shipping_info',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Shipping Information'
            )->addColumn(
                'qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Transfer Qty'
            )->addColumn(
                'qty_delivered',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Qty Delivered'
            )->addColumn(
                'qty_received',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Qty Received'
            );
        $installer->getConnection()->createTable($table);

        /**
         * create  os_transferstock_product table
         */

        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_transferstock_product'))
            ->addColumn(
                'transferstock_product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Transfer Stock Product Id'
            )
            ->addColumn(
                'transferstock_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Transfer Stock Id'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Product Id'
            )->addColumn(
                'product_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Product Name'
            )->addColumn(
                'product_sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Product SKU'
            )->addColumn(
                'qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Transfer Qty'
            )->addColumn(
                'qty_delivered',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Qty Delivered'
            )->addColumn(
                'qty_received',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Qty Received'
            )->addColumn(
                'transfer_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Transfer Stock Type'
            );
        $installer->getConnection()->createTable($table);


        /**
         * create os_transferstock_activity table
         */

        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_transferstock_activity'))
            ->addColumn(
                'activity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Activity Id'
            )->addColumn(
                'transferstock_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Transfer Stock ID'
            )->addColumn(
                'note',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Note'
            )->addColumn(
                'created_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Created By'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['default' => null],
                'Created At'
            )->addColumn(
                'activity_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Activity Type'
            )->addColumn(
                'total_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Total Qty'
            );
        $installer->getConnection()->createTable($table);

        /**
         * create  os_transferstock_activity_product table
         */

        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_transferstock_activity_product'))
            ->addColumn(
                'activity_product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Activity Product Id'
            )
            ->addColumn(
                'activity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Activity Id'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Product Id'
            )->addColumn(
                'product_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Product Name'
            )->addColumn(
                'product_sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Product SKU'
            )->addColumn(
                'qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Transfer Qty'
            );
        $installer->getConnection()->createTable($table);

        /**
         * Create table 'os_permission'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('os_permission'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'History ID'
            )
            ->addColumn(
                'user_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'user_id'
            )
            ->addColumn(
                'object_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                5,
                [],
                'Object Type'
            )
            ->addColumn(
                'object_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [],
                'Object Id'
            )
            ->addColumn(
                'role_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Role Id'
            )
            ->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'created_by',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Created By'
            )
            ->addIndex(
                $installer->getIdxName('os_permission', ['id']),
                ['id']
            )
            ->addIndex(
                $installer->getIdxName('os_permission', ['role_id']),
                ['role_id']
            )
            ->addIndex(
                $installer->getIdxName('os_permission', ['user_id']),
                ['user_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'os_permission',
                    'role_id',
                    'authorization_role',
                    'role_id'
                ),
                'role_id',
                $installer->getTable('authorization_role'),
                'role_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->addForeignKey(
                $installer->getFkName(
                    'os_permission',
                    'user_id',
                    'admin_user',
                    'user_id'
                ),
                'user_id',
                $installer->getTable('admin_user'),
                'user_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )
            ->setComment('OS Permission Table');
        $installer->getConnection()->createTable($table);

        /**
         * create os_warehouse_order_item table
         */
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_warehouse_order_item'))
            ->addColumn(
                'warehouse_order_item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Warehouse Sales Item Id'
            )->addColumn(
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Warehouse Id'
            )->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Sales Sales Id'
            )->addColumn(
                'item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Sales Item Id'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Product Id'
            )->addColumn(
                'qty_ordered',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Qty Ordered'
            )->addColumn(
                'qty_canceled',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Qty Canceled'
            )->addColumn(
                'subtotal',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Subtotal of order item'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addIndex(
                $installer->getIdxName('os_warehouse_order_item', ['warehouse_order_item_id']),
                ['warehouse_order_item_id']
            )->addForeignKey(
                $installer->getFkName(
                    'os_warehouse_order_item',
                    'item_id',
                    'sales_order_item',
                    'item_id'
                ),
                'item_id',
                $installer->getTable('sales_order_item'),
                'item_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);

        /**
         * create os_warehouse_shipment_item table
         */
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_warehouse_shipment_item'))
            ->addColumn(
                'warehouse_shipment_item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Warehouse Shipment Item Id'
            )->addColumn(
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Warehouse Id'
            )->addColumn(
                'shipment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Shipment Id'
            )->addColumn(
                'item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Shipment Item Id'
            )->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Sales Id'
            )->addColumn(
                'order_item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Sales Item Id'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Product Id'
            )->addColumn(
                'qty_shipped',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Qty Shipped'
            )->addColumn(
                'subtotal',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Subtotal of shipment item'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addIndex(
                $installer->getIdxName('os_warehouse_shipment_item', ['warehouse_shipment_item_id']),
                ['warehouse_shipment_item_id']
            )->addForeignKey(
                $installer->getFkName(
                    'os_warehouse_shipment_item',
                    'item_id',
                    'sales_shipment_item',
                    'entity_id'
                ),
                'item_id',
                $installer->getTable('sales_shipment_item'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);


        /**
         * create os_warehouse_creditmemo_item table
         */
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_warehouse_creditmemo_item'))
            ->addColumn(
                'warehouse_creditmemo_item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Warehouse Creditmemo Item Id'
            )->addColumn(
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Warehouse Id'
            )->addColumn(
                'creditmemo_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Creditmemo Id'
            )->addColumn(
                'item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Creditmeno Item Id'
            )->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Sales Id'
            )->addColumn(
                'order_item_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Sales Item Id'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Product Id'
            )->addColumn(
                'qty_refunded',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Qty Refunded'
            )->addColumn(
                'subtotal',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Subtotal of creditmemo item'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addIndex(
                $installer->getIdxName('os_warehouse_creditmemo_item', ['warehouse_creditmemo_item_id']),
                ['warehouse_creditmemo_item_id']
            )->addForeignKey(
                $installer->getFkName(
                    'os_warehouse_creditmemo_item',
                    'item_id',
                    'sales_creditmemo_item',
                    'entity_id'
                ),
                'item_id',
                $installer->getTable('sales_creditmemo_item'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);

        /**
         * create os_ship_product table
         */
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_ship_product'))
            ->addColumn(
                'ship_product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Ship Product Id'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['unique' => true, 'default' => null, 'unsigned' => true],
                'Product Id'
            )->addColumn(
                'qty_to_ship',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Qty to Ship'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )->addIndex(
                $installer->getIdxName('os_ship_product', ['ship_product_id']),
                ['ship_product_id']
            )->addForeignKey(
                $installer->getFkName(
                    'os_ship_product',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);


        /**
         * create os_lowstock_notification_rule table
         */
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_lowstock_notification_rule'))
            ->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Low Stock Notification Rule Id'
            )->addColumn(
                'rule_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Description'
            )->addColumn(
                'description',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Description'
            )->addColumn(
                'from_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['default' => null],
                'Start use rule'
            )->addColumn(
                'to_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['default' => null],
                'End rule'
            )->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['default' => 0],
                'Status'
            )->addColumn(
                'conditions_serialized',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Conditions of rule'
            )->addColumn(
                'priority',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null],
                'Priority'
            )->addColumn(
                'update_time_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['default' => null],
                'Update time type'
            )->addColumn(
                'specific_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => '00'],
                'Hours to check rule'
            )->addColumn(
                'specific_day',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => '1'],
                'Days to check rule'
            )->addColumn(
                'specific_month',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => '1'],
                'Months to check rule'
            )->addColumn(
                'lowstock_threshold_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['default' => 1],
                'Low stock threshold type'
            )->addColumn(
                'lowstock_threshold_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => 0],
                'Low stock threshold by product Qty'
            )->addColumn(
                'lowstock_threshold',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => 0],
                'Low stock threshold by sale days'
            )->addColumn(
                'sales_period',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => 0],
                'Sales Period'
            )->addColumn(
                'update_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['default' => null],
                'Update type'
            )->addColumn(
                'warehouse_ids',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Warehouse ids'
            )->addColumn(
                'notifier_emails',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null],
                'List emails to notify'
            )->addColumn(
                'warning_message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Message to notify'
            )->addColumn(
                'next_time_action',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['default' => null],
                'Next time to do action'
            )->addColumn(
                'apply',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['default' => 0],
                'Apply rule'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['default' => null],
                'Updated At'
            );
        $installer->getConnection()->createTable($table);

        /**
         * create os_lowstock_notification_rule_product table
         */
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_lowstock_notification_rule_product'))
            ->addColumn(
                'rule_product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Low Stock Notification Rule Product Id'
            )->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => 1, 'unsigned' => true],
                'Rule Id'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => 1, 'unsigned' => true],
                'Product Id'
            )->addIndex(
                $installer->getIdxName('os_lowstock_notification_rule_product', ['rule_id']),
                ['rule_id']
            )->addIndex(
                $installer->getIdxName('os_lowstock_notification_rule_product', ['product_id']),
                ['product_id']
            )->addForeignKey(
                $installer->getFkName(
                    'os_lowstock_notification_rule_product',
                    'rule_id',
                    'os_lowstock_notification_rule',
                    'rule_id'
                ),
                'rule_id',
                $installer->getTable('os_lowstock_notification_rule'),
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $installer->getFkName(
                    'os_lowstock_notification_rule_product',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $installer->getConnection()->createTable($table);

        /**
         * create os_lowstock_notification table
         */
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_lowstock_notification'))
            ->addColumn(
                'notification_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Low Stock Notification Id'
            )->addColumn(
                'rule_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => 1, 'unsigned' => true],
                'Rule Id'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'update_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['default' => null],
                'Update type'
            )->addColumn(
                'notifier_emails',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null],
                'List emails to notify'
            )->addColumn(
                'lowstock_threshold_type',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                1,
                ['default' => 1],
                'Low stock threshold type'
            )->addColumn(
                'lowstock_threshold_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => 0],
                'Low stock threshold by product Qty'
            )->addColumn(
                'lowstock_threshold',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => 0],
                'Low stock threshold by sale days'
            )->addColumn(
                'sales_period',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => 0],
                'Sales Period'
            )->addColumn(
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null],
                'Warehouse id'
            )->addColumn(
                'warehouse_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Warehouse name'
            )->addColumn(
                'warning_message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['default' => null],
                'Message to notify'
            );
        $installer->getConnection()->createTable($table);

        /**
         * create os_lowstock_notification_product table
         */
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_lowstock_notification_product'))
            ->addColumn(
                'notification_product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Low Stock Notification Product Id'
            )->addColumn(
                'notification_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => 1, 'unsigned' => true],
                'Low Stock Notification Id'
            )->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => null, 'unsigned' => true],
                'Product Id'
            )->addColumn(
                'product_sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Product Sku'
            )->addColumn(
                'product_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Product Name'
            )->addColumn(
                'current_qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                "12,4",
                ['default' => 0],
                'Current product qty'
            )->addColumn(
                'sold_per_day',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                "12,4",
                ['default' => 0],
                'Qty sold per day'
            )->addColumn(
                'total_sold',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                "12,4",
                ['default' => 0],
                'Total qty sold'
            )->addColumn(
                'availability_days',
                \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                null,
                ['default' => 0],
                'Number days that product is available to sell'
            )->addColumn(
                'availability_date',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['default' => null],
                'The date that product is available to sell'
            );
        $installer->getConnection()->createTable($table);

        /**
         * create os_increment_id table
         */
        $table  = $installer->getConnection()
            ->newTable($installer->getTable('os_increment_id'))
            ->addColumn(
                'increment_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Increment Id'
            )->addColumn(
                'code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['unique' => true, 'nullable' => false],
                'Entity Type Code'
            )->addColumn(
                'current_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => 1, 'unsigned' => true],
                'Current Id'
            )->addIndex(
                $installer->getIdxName('os_increment_id', ['increment_id']),
                ['increment_id']
            );
        $installer->getConnection()->createTable($table);


        $installer->endSetup();
        return $this;
    }


}
