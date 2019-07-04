<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel;

use Magestore\InventorySuccess\Api\Db\QueryProcessorInterface;

use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product;
use Magestore\InventorySuccess\Model\Warehouse\Options\Status;
use Magestore\InventorySuccess\Model\Warehouse\Options\PrimaryWarehouse;
use \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product as WarehouseProductResource;
use \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface;
use \Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;

class InstallManagement extends AbstractResource
{
    /**
     * Default Warehouse ID
     * @var int
     */
    private $_warehouseId;

    /**
     * @var
     */
    protected $_mainTableQuery;
    protected $_joinTableQuery;
    protected $_conditionQuery;
    protected $_primaryField;

    const STEP_CONVERT_ORDER_ITEM = 'convert_order_item';
    const STEP_CONVERT_ORDER = 'convert_order';
    const STEP_CONVERT_ORDER_GRID = 'convert_order_grid';
    const STEP_CONVERT_SHIPMENT_ITEM = 'convert_shipment_item';
    const STEP_CONVERT_CREDITMEMO_ITEM = 'convert_creditmemo_item';

    /**
     *
     */
    protected function _construct()
    {
        //todo
        $this->_mainTableQuery = array(
            self::STEP_CONVERT_ORDER_ITEM => $this->getTable('os_warehouse_order_item'),
            self::STEP_CONVERT_ORDER => $this->getTable('os_warehouse_order_item'),
            self::STEP_CONVERT_ORDER_GRID => $this->getTable('os_warehouse_order_item'),
            self::STEP_CONVERT_SHIPMENT_ITEM => $this->getTable('os_warehouse_shipment_item'),
            self::STEP_CONVERT_CREDITMEMO_ITEM => $this->getTable('os_warehouse_creditmemo_item'),
        );
        $this->_joinTableQuery = array(
            self::STEP_CONVERT_ORDER_ITEM => $this->getTable('sales_order_item'),
            self::STEP_CONVERT_ORDER => $this->getTable('sales_order'),
            self::STEP_CONVERT_ORDER_GRID => $this->getTable('sales_order_grid'),
            self::STEP_CONVERT_SHIPMENT_ITEM => $this->getTable('sales_shipment_item'),
            self::STEP_CONVERT_CREDITMEMO_ITEM => $this->getTable('sales_creditmemo_item'),
        );
        $this->_conditionQuery = array(
            self::STEP_CONVERT_ORDER_ITEM => 'main_table.order_id = order.order_id and main_table.item_id = order.item_id and main_table.product_id = order.product_id',
            self::STEP_CONVERT_ORDER => 'main_table.order_id = order.entity_id',
            self::STEP_CONVERT_ORDER_GRID => 'main_table.order_id = order.entity_id',
            self::STEP_CONVERT_SHIPMENT_ITEM => 'main_table.shipment_id = order.parent_id and main_table.order_item_id = order.order_item_id and main_table.product_id = order.product_id',
            self::STEP_CONVERT_CREDITMEMO_ITEM => 'main_table.creditmemo_id = order.parent_id and main_table.order_item_id = order.order_item_id and main_table.product_id = order.product_id',
        );
        $this->_primaryField = array(
            self::STEP_CONVERT_ORDER_ITEM => 'item_id',
            self::STEP_CONVERT_ORDER => 'entity_id',
            self::STEP_CONVERT_ORDER_GRID => 'entity_id',
            self::STEP_CONVERT_SHIPMENT_ITEM => 'entity_id',
            self::STEP_CONVERT_CREDITMEMO_ITEM => 'entity_id',
        );
    }

    /**
     * @inheritdoc
     */
    public function calculateQtyToShip()
    {
        /* start query process */
        $this->_queryProcessor->start();

        /* scan items then prepare to add to warehouse */
        //$qtys = $this->_scanOrderItems();
        //$this->_scanShipmentItems();
        //$this->_scanCreditmemoItems();

        $qtys = $this->_updateOrderItems();
        $this->_updateShipmentItems();
        $this->_updateCreditmemoItems();

        /* prepare update qty-to-ship to warehouse */
        $this->_prepareQtyToShipWarehouse($qtys);

        /* process queries in Processor */
        $this->_queryProcessor->process();

        return $this;
    }

    /**
     * Create default warehouse
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\InstallManagement
     */
    public function createDefaultWarehouse()
    {
        /* start query process */
        $this->_queryProcessor->start();

        /* prepare query to calculate qty-to-ship */
        $this->_prepareCreateDefaultWarehouse();

        /* process queries in Processor */
        $this->_queryProcessor->process();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function transferProductsToDefaultWarehouse()
    {
        $this->createDefaultWarehouse();

        /* start query process */
        $this->_queryProcessor->start();

        /* prepare query to transfer products to default warehouse */
        $this->_prepareTransferProductsToDefaultWarehouse();

        /* process queries in Processor */
        $this->_queryProcessor->process();

        return $this;
    }

    /**
     * Get default warehouseId
     *
     * @return int
     */
    protected function _getDefaultWarehouseId()
    {
        if (!$this->_warehouseId) {
            $connection = $this->getConnection();
            /* get default warehouse Id */
            $select = $connection->select()
                ->from($this->getTable('os_warehouse'), ['warehouse_id'])
                ->where('is_primary = ?', PrimaryWarehouse::STATUS_IS_PRIMARY)
                ->limit(1);
            $query = $connection->query($select);
            $row = $query->fetch();
            $this->_warehouseId = $row['warehouse_id'];
        }
        return $this->_warehouseId;
    }

    /**
     * Get all warehouse ids
     *
     * @return array
     */
    protected function _getAllWarehouseIds()
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('os_warehouse'), ['warehouse_id']);
        $query = $connection->query($select);
        $warehouseIds = [];
        while ($row = $query->fetch()) {
            $warehouseIds[] = $row['warehouse_id'];
        }
        return $warehouseIds;
    }

    /**
     * Prepare to transfer products to default warehouse
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\InstallManagement
     */
    protected function _prepareTransferProductsToDefaultWarehouse()
    {
        $products = [];
        $batch = 0;
        $connection = $this->getConnection();

        /* get default warehouse Id */
        $warehouseId = $this->_getDefaultWarehouseId();

        /* load all stock items */
        $select = $connection->select()
            ->from($this->getTable(WarehouseProductResource::MAIN_TABLE), '*');
        $query = $connection->query($select);
        $values = [];
        while ($row = $query->fetch()) {
            if (!isset($products[$batch])) {
                $products[$batch] = [];
            }
            unset($row[WarehouseProductResource::PRIMARY_KEY]);
            $row[WarehouseProductInterface::WAREHOUSE_ID] = $warehouseId;
            $row[WarehouseProductInterface::WEBSITE_ID] = $warehouseId;
            $row[WarehouseProductInterface::STOCK_ID] = $warehouseId;
            $row[WarehouseProductInterface::TOTAL_QTY] = $row['qty'] ? $row['qty'] : 0;
            $products[$batch][] = $row;
            if (count($products[$batch]) > 900) {
                $batch++;
            }
        }

        /* add query to Processor */
        foreach ($products as $batch => $items) {
            $this->_queryProcessor->addQuery([
                'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
                'values' => $items,
                'table' => $this->getTable(WarehouseProductResource::MAIN_TABLE)
            ]);
        }
        return $this;
    }

    /**
     * Prepare to create default warehouse
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\InstallManagement
     */
    protected function _prepareCreateDefaultWarehouse()
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getTable('os_warehouse'), ['warehouse_id'])
            ->limit(1);

        $query = $connection->query($select);
        if (count($query->fetchAll())) {
            return $this;
        }

        $primaryWHData = [
            0 => [
                WarehouseInterface::WAREHOUSE_ID => WarehouseInterface::DEFAULT_WAREHOUSE_ID,
                WarehouseInterface::WAREHOUSE_NAME => __('Primary Location'),
                WarehouseInterface::WAREHOUSE_CODE => 'primary',
                WarehouseInterface::IS_PRIMARY => PrimaryWarehouse::STATUS_IS_PRIMARY,
                'street' => '6146 Honey Bluff Parkway',
                'city' => 'Calder',
                'region' => 'Michigan',
                'region_id' => 33,
                'country_id' => 'US',
                'postcode' => '49628-7978',
                'country' => 'United State'
            ]
        ];

        $primaryStockData = [
            0 => [
                'stock_id' => $primaryWHData[0][WarehouseInterface::WAREHOUSE_ID],
                'website_id' => $primaryWHData[0][WarehouseInterface::WAREHOUSE_ID],
                'stock_name' => $primaryWHData[0][WarehouseInterface::WAREHOUSE_CODE],
            ]
        ];

        /* add query to Processor */
        $this->_queryProcessor->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
            'values' => $primaryWHData,
            'table' => $this->getTable('os_warehouse')
        ]);

        $this->_queryProcessor->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
            'values' => $primaryStockData,
            'table' => $this->getTable('cataloginventory_stock')
        ]);

        return $this;
    }

    /**
     * Prepare calculating qty-to-ship
     *
     * @return array
     */
    protected function _scanOrderItems()
    {
        $items = [];
        $products = [];
        //$needToShipOrderIds = $this->getNeedToShipOrderIds();
        $connection = $this->getConnection();
        $warehouseId = $this->_getDefaultWarehouseId();
        /* Get order items */
        //$orderCondition = $connection->prepareSqlCondition('order_id', ['in' => $needToShipOrderIds]);        
        $select = $connection->select()->from(['main_table' => $this->getTable('sales_order_item')], [
            'item_id',
            'order_id',
            'product_id',
            'qty_ordered',
            'qty_canceled',
            'subtotal' => 'base_row_total',
            'qty_to_ship' => "IF(qty_ordered-qty_shipped-qty_refunded-qty_canceled > '0', qty_ordered-qty_shipped-qty_refunded-qty_canceled, 0)",
        ])
            //->where($orderCondition)
            //->where('qty_ordered-qty_shipped-qty_refunded-qty_canceled > ?', 0)
            ->where('product_type = ?', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
            ->joinLeft(['order' => $this->getTable('sales_order')],
                'main_table.order_id = order.entity_id', [
                    'created_at', 'updated_at'
                ]);
        $query = $connection->query($select);
        while ($row = $query->fetch()) {
            /* prepare qty_to_ship data of product in warehouse */
            $productId = $row['product_id'];
            $qtyToShip = $row['qty_to_ship'];
            if (isset($products[$productId])) {
                $qtyToShip += $products[$productId]['qty_to_ship'];
            }
            $products[$productId] = ['product_id' => $productId, 'qty_to_ship' => $qtyToShip];
            /* prepare data of orderItem in os_warehouse_order_item */
            $row['warehouse_id'] = $warehouseId;
            unset($row['qty_to_ship']);
            $items[] = $row;
        }

        /* insert qty_to_ship data of products */
        /*
        $this->_queryProcessor->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
            'values' => $products,
            'table' => $this->getTable('os_ship_product')
        ]);
        */
        /* insert items to warehouse_order_item */
        $this->_queryProcessor->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
            'values' => $items,
            'table' => $this->getTable('os_warehouse_order_item')
        ]);

        return $products;
    }

    /**
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\InstallManagement
     */
    protected function _scanShipmentItems()
    {
        $items = [];
        $connection = $this->getConnection();
        $warehouseId = $this->_getDefaultWarehouseId();
        /* Get shipment items */
        $select = $connection->select()->from(['main_table' => $this->getTable('sales_shipment_item')], [
            'shipment_id' => 'parent_id',
            'item_id' => 'entity_id',
            'order_item_id',
            'product_id',
            'qty_shipped' => 'qty',
            'subtotal' => 'price',
        ])
            ->joinLeft(['shipment' => $this->getTable('sales_shipment')],
                'main_table.parent_id = shipment.entity_id', [
                    'order_id', 'created_at', 'updated_at'
                ]);
        $query = $connection->query($select);
        while ($row = $query->fetch()) {
            $row['warehouse_id'] = $warehouseId;
            $items[] = $row;
        }

        /* insert items to warehouse_shipment_item */
        $this->_queryProcessor->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
            'values' => $items,
            'table' => $this->getTable('os_warehouse_shipment_item')
        ]);
        return $this;
    }

    /**
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\InstallManagement
     */
    protected function _scanCreditmemoItems()
    {
        $items = [];
        $connection = $this->getConnection();
        $warehouseId = $this->_getDefaultWarehouseId();
        /* Get shipment items */
        $select = $connection->select()->from(['main_table' => $this->getTable('sales_creditmemo_item')], [
            'creditmemo_id' => 'parent_id',
            'item_id' => 'entity_id',
            'order_item_id',
            'product_id',
            'qty_refunded' => 'qty',
            'subtotal' => 'base_row_total',
        ])
            ->joinLeft(['creditmemo' => $this->getTable('sales_creditmemo')],
                'main_table.parent_id = creditmemo.entity_id', [
                    'order_id', 'created_at', 'updated_at'
                ]);
        $query = $connection->query($select);
        while ($row = $query->fetch()) {
            $row['warehouse_id'] = $warehouseId;
            $items[] = $row;
        }

        /* insert items to warehouse_creditmemo_item */
        $this->_queryProcessor->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
            'values' => $items,
            'table' => $this->getTable('os_warehouse_creditmemo_item')
        ]);
        return $this;
    }

    /**
     * Prepare update qty-to-ship to warehouse
     *
     * @param array $qtys
     * @return \Magestore\InventorySuccess\Model\ResourceModel\InstallManagement
     */
    protected function _prepareQtyToShipWarehouse($qtys)
    {
        $connection = $this->getConnection();
        if (!count($qtys)) {
            return $this;
        }
        $warehouseId = $this->_getDefaultWarehouseId();
        $conditions = [];
        foreach ($qtys as $productId => $item) {
            $case = $connection->quoteInto('?', $productId);
            $totalQtyResult = $connection->quoteInto('total_qty+?', $item['qty_to_ship']);
            $conditions['total_qty'][$case] = $totalQtyResult;
        }
        $values = [
            'total_qty' => $connection->getCaseSql('product_id', $conditions['total_qty'], 'total_qty'),
        ];
        $where = [
            'product_id IN (?)' => array_keys($qtys),
            WarehouseProductInterface::WAREHOUSE_ID . '=?' => $warehouseId
        ];

        /* add query to the processor */
        $this->_queryProcessor->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => $values,
            'condition' => $where,
            'table' => $this->getTable(WarehouseProductResource::MAIN_TABLE)
        ]);

        return $this;
    }

    /**
     *
     * @return array
     */
    public function getNeedToShipOrderIds()
    {
        $orderIds = [];
        $connection = $this->getConnection();
        $condition = $connection->prepareSqlCondition('status', ['nin' => ['complete', 'closed', 'canceled']]);
        $select = $connection->select()
            ->from($this->getTable('sales_order'), ['entity_id'])
            ->where($condition);

        $query = $connection->query($select);
        while ($row = $query->fetch()) {
            $orderIds[] = $row['entity_id'];
        }
        return $orderIds;
    }

    /**
     * transfer data in os_warehouse_product to cataloginventory_stock_item
     * use to upgrade Magestore_InventorySuccess from v1.0.0 to v1.1.0 and higher
     *
     */
    public function transferWarehouseProductToMagentoStockItem()
    {
        $this->_queryProcessor->start();
        $this->_queryProcessor->addQuery($this->prepareTransferWarehouse());
        $this->_queryProcessor->addQueries($this->prepareTransferWarehouseStocks());
        $this->_queryProcessor->addQueries($this->prepareTransferCompositeProducts());
        $this->_queryProcessor->process();
    }

    /**
     * create new stocks for warehouses
     *
     * @return array
     */
    public function prepareTransferWarehouse()
    {
        $stocks = [];
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getTable('os_warehouse'), '*');
        $query = $connection->query($select);
        while ($row = $query->fetch()) {
            $stocks[] = [
                'stock_id' => $row[WarehouseInterface::WAREHOUSE_ID],
                'website_id' => $row[WarehouseInterface::WAREHOUSE_ID],
                'stock_name' => $row[WarehouseInterface::WAREHOUSE_CODE]
            ];
        }
        /* insert stocks to cataloginventory_stock */
        return [
            'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
            'values' => $stocks,
            'table' => $this->getTable('cataloginventory_stock')
        ];
    }

    /**
     * transfer data from warehouse product to stock item
     *
     * @return array
     */
    public function prepareTransferWarehouseStocks()
    {
        $queries = [];
        $stockItems = [];
        $batch = 0;
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getTable('os_warehouse_product'), '*');
        $query = $connection->query($select);

        while ($row = $query->fetch()) {
            if (!isset($row['warehouse_id']) || !$row['warehouse_id']) {
                continue;
            }
            if (!isset($stockItems[$batch])) {
                $stockItems[$batch] = [];
            }
            $stockItems[$batch][] = [
                WarehouseProductInterface::WEBSITE_ID => $row['warehouse_id'],
                WarehouseProductInterface::STOCK_ID => $row['warehouse_id'],
                WarehouseProductInterface::PRODUCT_ID => $row[WarehouseProductInterface::PRODUCT_ID],
                WarehouseProductInterface::TOTAL_QTY => $row[WarehouseProductInterface::TOTAL_QTY],
                WarehouseProductInterface::AVAILABLE_QTY => $row[WarehouseProductInterface::TOTAL_QTY] - $row[WarehouseProductInterface::QTY_TO_SHIP],
                WarehouseProductInterface::SHELF_LOCATION => $row[WarehouseProductInterface::SHELF_LOCATION],
                'stock_status_changed_auto' => 1,
                'is_in_stock' => 1,
            ];
            if (count($stockItems[$batch]) > 900) {
                $batch++;
            }
        }

        /* prepare queries to insert data to cataloginventory_stock_item */
        foreach ($stockItems as $batch => $items) {
            $queries[] = [
                'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
                'values' => $items,
                'table' => $this->getTable('cataloginventory_stock_item')
            ];
        }

        return $queries;
    }

    /**
     * transfer composite products to primary warehouse
     *
     * @return array
     */
    public function prepareTransferCompositeProducts()
    {
        $queries = [];
        $productIds = [];
        $connection = $this->getConnection();

        /* load entity_id of composite products */
        $compositeTypes = [
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE,
            \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE,
            \Magento\Bundle\Model\Product\Type::TYPE_CODE,
        ];

        $select = $connection->select()->from(['p' => $this->getTable('catalog_product_entity')], 'entity_id');
        $select->where('p.type_id IN (\'' . implode('\',\'', $compositeTypes) . '\')');

        $query = $connection->query($select);
        while ($row = $query->fetch()) {
            $productIds[] = $row['entity_id'];
        }
        if (!count($productIds)) {
            return $queries;
        }

        /* get default warehouse Id */
        $warehouseId = $this->_getDefaultWarehouseId();

        /* load all stock items */
        $select = $connection->select()
            ->from(['stock' => $this->getTable(WarehouseProductResource::MAIN_TABLE)], '*');
        $select->where('stock.product_id IN (' . implode(',', $productIds) . ')');
        $query = $connection->query($select);
        $stockItems = [];
        $batch = 0;
        while ($row = $query->fetch()) {
            if (!isset($stockItems[$batch])) {
                $stockItems[$batch] = [];
            }
            unset($row[WarehouseProductResource::PRIMARY_KEY]);
            $row[WarehouseProductInterface::WAREHOUSE_ID] = $warehouseId;
            $row[WarehouseProductInterface::WEBSITE_ID] = $warehouseId;
            $row[WarehouseProductInterface::STOCK_ID] = $warehouseId;
            $row[WarehouseProductInterface::TOTAL_QTY] = $row['qty'] ? $row['qty'] : 0;
            $stockItems[$batch][] = $row;
            if (count($stockItems[$batch]) > 900) {
                $batch++;
            }
        }

        /* prepare queries to insert data to cataloginventory_stock_item */
        foreach ($stockItems as $batch => $items) {
            $queries[] = [
                'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
                'values' => $items,
                'table' => $this->getTable('cataloginventory_stock_item')
            ];
        }

        return $queries;
    }

    /**
     * convert data from os_warehouse_order_item into sales_order_item
     * convert data from os_warehouse_shipment_item into sales_shipment_item
     * convert data from os_warehouse_creditmemo_item into sales_creditmemo_item
     */
    public function convertSaleItemsData()
    {
        /* start query process */
        $this->_queryProcessor->start();

        /* convert items then prepare to add to query */
        $this->_convertOrderItems(self::STEP_CONVERT_ORDER_ITEM);
        $this->_convertOrderItems(self::STEP_CONVERT_ORDER);
        $this->_convertOrderItems(self::STEP_CONVERT_ORDER_GRID);
        $this->_convertOrderItems(self::STEP_CONVERT_SHIPMENT_ITEM);
        $this->_convertOrderItems(self::STEP_CONVERT_CREDITMEMO_ITEM);

        /* process queries in Processor */
        $this->_queryProcessor->process();
    }

    /**
     * convert order/shipment/creditmemo items
     *
     * @return array
     */
    public function _convertOrderItems($type)
    {
        $this->_queryProcessor->start();
        if (!$type) {
            return array();
        }
        $warehouseId = $this->_getDefaultWarehouseId();
        $connection = $this->_getConnection('read');
        /* Get order items */
        $select = $connection->select()->from(array('main_table' => $this->_mainTableQuery[$type]), array(
            'warehouse_id',
        ))
            ->joinLeft(array('order' => $this->_joinTableQuery[$type]),
                $this->_conditionQuery[$type], array(
                    'item_id_convert' => 'order.' . $this->_primaryField[$type]
                )
            );
        $query = $connection->query($select);
        $conditions = array();
        $itemIdsArray = array();
        while ($row = $query->fetch()) {
            if (!$row['item_id_convert']) {
                continue;
            }
            $itemIdsArray[] = $row['item_id_convert'];
            $case = $connection->quoteInto('?', $row['item_id_convert']);
            $warehouse_id = $row['warehouse_id'] ? $row['warehouse_id'] : $warehouseId;
            $conditions['convert'][$case] = $warehouse_id;
        }
        if (!$conditions) {
            return $this;
        }
        $values = array(
            'warehouse_id' => $connection->getCaseSql($this->_primaryField[$type], $conditions['convert'], $warehouseId),
        );
        $where = array($this->_primaryField[$type] . ' IN (?)' => $itemIdsArray);
        /* query to update warehouse_id */
        $this->_queryProcessor->addQuery(array(
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => $values,
            'condition' => $where,
            'table' => $this->_joinTableQuery[$type]
        ));
        $this->_queryProcessor->process();
        return $this;
    }

    /**
     * Prepare calculating qty-to-ship
     * Prepare query update into sales_order_item table
     *
     * @return array
     */
    protected function _updateOrderItems()
    {
        $products = [];
        //$needToShipOrderIds = $this->getNeedToShipOrderIds();
        $connection = $this->getConnection();
        $warehouseId = $this->_getDefaultWarehouseId();
        /* Get order items */
        //$orderCondition = $connection->prepareSqlCondition('order_id', ['in' => $needToShipOrderIds]);
        $select = $connection->select()->from(['main_table' => $this->getTable('sales_order_item')], [
            'item_id',
            'order_id',
            'product_id',
            'qty_ordered',
            'qty_canceled',
            'subtotal' => 'base_row_total',
            'qty_to_ship' => "IF(qty_ordered-qty_shipped-qty_refunded-qty_canceled > '0', qty_ordered-qty_shipped-qty_refunded-qty_canceled, 0)",
        ])
            //->where($orderCondition)
            //->where('qty_ordered-qty_shipped-qty_refunded-qty_canceled > ?', 0)
            ->where('product_type = ?', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
            ->joinLeft(['order' => $this->getTable('sales_order')],
                'main_table.order_id = order.entity_id', [
                    'created_at', 'updated_at'
                ]);
        $query = $connection->query($select);
        $ids = array();
        while ($row = $query->fetch()) {
            $ids[] = $row['item_id'];
            /* prepare qty_to_ship data of product in warehouse */
            $productId = $row['product_id'];
            $qtyToShip = $row['qty_to_ship'];
            if (isset($products[$productId])) {
                $qtyToShip += $products[$productId]['qty_to_ship'];
            }
            $products[$productId] = ['product_id' => $productId, 'qty_to_ship' => $qtyToShip];
        }
        if (count($ids)) {
            /* prepare query to update in sales_order_item table */
            $updateValues = array(
                'warehouse_id' => $warehouseId,
            );
            $field = 'item_id';
            $where = $connection->quoteInto($this->getTable('sales_order_item') . '.' . $field . ' IN (?) ', $ids);
            $this->_queryProcessor->addQuery(array(
                'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
                'values' => $updateValues,
                'condition' => $where,
                'table' => $this->getTable('sales_order_item')
            ));
        }
        return $products;
    }

    /**
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\InstallManagement
     */
    protected function _updateShipmentItems()
    {
        $connection = $this->getConnection();
        $warehouseId = $this->_getDefaultWarehouseId();
        /* Get shipment items */
        $select = $connection->select()->from(['main_table' => $this->getTable('sales_shipment_item')], [
            'item_id' => 'entity_id',
        ]);
        $query = $connection->query($select);
        $ids = array();
        while ($row = $query->fetch()) {
            $ids[] = $row['item_id'];
        }
        if (!count($ids)) {
            return $this;
        }
        /* prepare query to update in sales_shipment_item table */
        $updateValues = array(
            'warehouse_id' => $warehouseId,
        );
        $field = 'entity_id';
        $where = $connection->quoteInto($this->getTable('sales_shipment_item') . '.' . $field . ' IN (?) ', $ids);
        $this->_queryProcessor->addQuery(array(
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => $updateValues,
            'condition' => $where,
            'table' => $this->getTable('sales_shipment_item')
        ));
        return $this;
    }

    /**
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\InstallManagement
     */
    protected function _updateCreditmemoItems()
    {
        $connection = $this->getConnection();
        $warehouseId = $this->_getDefaultWarehouseId();
        /* Get shipment items */
        $select = $connection->select()->from(['main_table' => $this->getTable('sales_creditmemo_item')], [
            'item_id' => 'entity_id',
        ]);
        $query = $connection->query($select);
        $ids = array();
        while ($row = $query->fetch()) {
            $ids[] = $row['item_id'];
        }
        if (!count($ids)) {
            return $this;
        }
        /* prepare query to update in sales_creditmemo_item table */
        $updateValues = array(
            'warehouse_id' => $warehouseId,
        );
        $field = 'entity_id';
        $where = $connection->quoteInto($this->getTable('sales_creditmemo_item') . '.' . $field . ' IN (?) ', $ids);
        /* insert items to warehouse_creditmemo_item */
        $this->_queryProcessor->addQuery(array(
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => $updateValues,
            'condition' => $where,
            'table' => $this->getTable('sales_creditmemo_item')
        ));
        return $this;
    }

    /**
     * Update stock id value base on website id in table cataloginventory_stock_item
     * Magento 2 update unique fields of table cataloginventory_stock_item
     * from 'product_id' and 'website_id' to 'product_id' and 'stock_id'
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\InstallManagement
     */
    public function updateStockId()
    {
        $this->_queryProcessor->start();

        $connection = $this->getConnection();

        $warehouseIds = $this->_getAllWarehouseIds();

        $field = WarehouseProductInterface::WAREHOUSE_ID;

        $where = $connection->quoteInto($this->getTable(Product::MAIN_TABLE) . ".{$field} IN (?) ", $warehouseIds);

        $this->_queryProcessor->addQuery(array(
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => [WarehouseProductInterface::STOCK_ID => new \Zend_Db_Expr(WarehouseProductInterface::WAREHOUSE_ID)],
            'condition' => $where,
            'table' => $this->getTable(Product::MAIN_TABLE)
        ));
        $this->_queryProcessor->process();
    }
}
