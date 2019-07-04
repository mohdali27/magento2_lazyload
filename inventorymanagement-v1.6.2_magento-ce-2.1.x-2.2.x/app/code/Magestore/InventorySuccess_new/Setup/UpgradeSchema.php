<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Upgrade the Cms module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addStoreIdField($setup);
            $this->addTotalQtyFieldToStockItem($setup);
            $this->addShelfLocationFieldToStockItem($setup);
        }
        
        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $this->addWarehouseStoreViewTable($setup);
        }
        
        if (version_compare($context->getVersion(), '1.1.2', '<')) {
            $this->addUpdatedTimeFieldStockItem($setup);
        }
        
        if (version_compare($context->getVersion(), '1.2.2', '<')) {
            $this->addWarehouseIdToItem($setup);
        }
        
        if (version_compare($context->getVersion(), '1.2.3', '<')) {
            $this->updateOmniChannel($setup);
        }

        
        if (version_compare($context->getVersion(), '1.2.4', '<')) {
            $this->createStockTransferTable($setup);
            $this->addStockTransferIdToStockMovement($setup);
            $this->addWarehouseIdColumnToOrder($setup);
        }

        if (version_compare($context->getVersion(), '1.6.1.2', '<')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('os_warehouse'),
                'country',
                array(
                    'type'      => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable'  => true,
                    'length'    => '127',
                    'comment'   => 'country'
                )
            );
        }

    }

    /**
     * Add store_id
     *
     * @param SchemaSetupInterface $setup
     * @return \Magestore\InventorySuccess\Setup\UpgradeSchema
     */
    protected function addStoreIdField(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('os_warehouse'), 'store_id')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('os_warehouse'),
                'store_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => true,
                    'comment' => 'Store View Id'
                ]
            );
        }
        return $this;
    }

    /**
     *
     * @param SchemaSetupInterface $setup
     * @return \Magestore\InventorySuccess\Setup\UpgradeSchema
     */
    protected function addTotalQtyFieldToStockItem(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('cataloginventory_stock_item'), 'total_qty')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('cataloginventory_stock_item'),
                'total_qty',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0.0000',
                    'comment' => 'Total Qty',
                    'after' => 'qty'
                ]
            );
        }
        return $this;
    }

    /**
     *
     * @param SchemaSetupInterface $setup
     * @return \Magestore\InventorySuccess\Setup\UpgradeSchema
     */
    protected function addShelfLocationFieldToStockItem(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('cataloginventory_stock_item'), 'shelf_location')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('cataloginventory_stock_item'),
                'shelf_location',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'comment' => 'Shelf Location',
                    'after' => 'total_qty'
                ]
            );
        }
        return $this;
    }

    /**
     *
     * @param SchemaSetupInterface $setup
     * @return \Magestore\InventorySuccess\Setup\UpgradeSchema
     */
    protected function addWarehouseStoreViewTable(SchemaSetupInterface $setup)
    {
        $setup->startSetup();
        $setup->getConnection()->dropTable($setup->getTable('os_warehouse_store_view'));
        /**
         * create os_warehouse_store_view table
         */
        $table = $setup->getConnection()
            ->newTable($setup->getTable('os_warehouse_store_view'))
            ->addColumn(
                'id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['unsigned' => true, 'nullable' => false],
                'Warehouse Id'
            )
            ->addColumn(
                'store_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                6,
                ['unsigned' => true, 'nullable' => false],
                'Store View Id'
            )->addIndex(
                $setup->getIdxName('os_warehouse_store_view', ['warehouse_id']),
                ['warehouse_id']
            )->addIndex(
                $setup->getIdxName('os_warehouse_store_view', ['store_id']),
                ['store_id']
            )->addIndex(
                $setup->getIdxName(
                    'os_warehouse_store_view',
                    ['warehouse_id', 'store_id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                ),
                ['warehouse_id', 'store_id'],
                ['type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE]
            )->addForeignKey(
                $setup->getFkName(
                    'os_warehouse_store_view',
                    'warehouse_id',
                    'os_warehouse',
                    'warehouse_id'
                ),
                'warehouse_id',
                $setup->getTable('os_warehouse'),
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            )->addForeignKey(
                $setup->getFkName(
                    'os_warehouse_store_view',
                    'store_id',
                    'store',
                    'store_id'
                ),
                'store_id',
                $setup->getTable('store'),
                'store_id',
                \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
            );
        $setup->getConnection()->createTable($table);

        $setup->endSetup();
        return $this;
    }

    /**
     *
     * @param SchemaSetupInterface $setup
     * @return \Magestore\InventorySuccess\Setup\UpgradeSchema
     */
    protected function addUpdatedTimeFieldStockItem(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('cataloginventory_stock_item'), 'updated_time')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('cataloginventory_stock_item'),
                'updated_time',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE,
                    'comment' => 'Updated Time'
                ]
            );
        }
        return $this;
    }
    
    /**
     * Add warehouse_id to order_item, shipment_item & creditmemo_item
     * 
     * @param SchemaSetupInterface $setup
     * @return \Magestore\InventorySuccess\Setup\UpgradeSchema
     */
    protected function addWarehouseIdToItem(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('sales_order_item'), 'warehouse_id')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_item'),
                'warehouse_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'length' => 11,
                    'unsigned' => true,
                    'comment' => 'Warehouse Id'
                ]
            );
        }
        
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('sales_shipment_item'), 'warehouse_id')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_shipment_item'),
                'warehouse_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'length' => 11,
                    'unsigned' => true,
                    'comment' => 'Warehouse Id'
                ]
            );
        }
        
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('sales_creditmemo_item'), 'warehouse_id')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_creditmemo_item'),
                'warehouse_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'length' => 11,
                    'unsigned' => true,
                    'comment' => 'Warehouse Id'
                ]
            );
        }

        if (!$setup->getConnection()->tableColumnExists($setup->getTable('sales_order'), 'warehouse_id')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'warehouse_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'length' => 11,
                    'unsigned' => true,
                    'comment' => 'Warehouse Id'
                ]
            );
        }

        if (!$setup->getConnection()->tableColumnExists($setup->getTable('sales_order_grid'), 'warehouse_id')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_grid'),
                'warehouse_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'length' => 11,
                    'unsigned' => true,
                    'comment' => 'Warehouse Id'
                ]
            );
        }
        return $this;        
    }


    /**
     *
     *
     * @param SchemaSetupInterface $setup
     * @return \Magestore\InventorySuccess\Setup\UpgradeSchema
     */
    protected function updateOmniChannel(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('os_stocktaking_product'), 'stocktaking_reason')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('os_stocktaking_product'),
                'stocktaking_reason',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => 255,
                    'unsigned' => true,
                    'comment' => 'Stocktaking reason'
                ]
            );
        }

        if (!$setup->getConnection()->tableColumnExists($setup->getTable('os_transferstock_product'), 'qty_returned')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('os_transferstock_product'),
                'qty_returned',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => false,
                    'default' => '0.0000',
                    'unsigned' => true,
                    'comment' => 'Qty Returned'
                ]
            );
        }
        return $this;
    }
    
    /**
     *
     *
     * @param SchemaSetupInterface $setup
     * @return \Magestore\InventorySuccess\Setup\UpgradeSchema
     */    
    protected function createStockTransferTable(SchemaSetupInterface $setup)
    {
        
        if($setup->getConnection()->isTableExists($setup->getTable('os_stock_transfer'))) {
            return $this;
        }

        /**
         * create os_stock_transfer table
         */
        $table  = $setup->getConnection()
            ->newTable($setup->getTable('os_stock_transfer'))
            ->addColumn(
                'stock_transfer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Stock Transfer Id'
            )->addColumn(
                'transfer_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['default' => null],
                'Transfer Code'
            )->addColumn(
                'qty',
                \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                '12,4',
                ['default' => 0],
                'Total Item'
            )->addColumn(
                'total_sku',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['default' => 0],
                'Total SKU'
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
                'Reference Number'
            )->addColumn(
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['nullable' => true, 'default' => null],
                'Warehouse Id'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                'Created At'
            );
        
        $setup->getConnection()->createTable($table);        
    }
    
    /**
     *
     * @param SchemaSetupInterface $setup
     * @return \Magestore\InventorySuccess\Setup\UpgradeSchema
     */
    protected function addStockTransferIdToStockMovement(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('os_stock_movement'), 'stock_transfer_id')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('os_stock_movement'),
                'stock_transfer_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'nullable' => true,
                    'length' => 11,
                    'unsigned' => true,
                    'comment' => 'Stock tranfer id'
                ]
            );
        }
        return $this;
    }    

    public function addWarehouseIdColumnToOrder(SchemaSetupInterface $setup)
    {
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('sales_order'), 'warehouse_id')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('sales_order_grid'), 'warehouse_id')) {
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order_grid'),
                'warehouse_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
            );
        }
    }

}
