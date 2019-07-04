<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\Warehouse;

use Magestore\InventorySuccess\Model\ResourceModel\AbstractResource;
use Magestore\InventorySuccess\Api\Db\QueryProcessorInterface;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product as WarehouseProductResource;

class WarehouseStockRegistry extends AbstractResource
{
    protected function _construct()
    {
        /* do nothing */
    }
    
    /**
     * Update shelf locations of products in Warehouse
     * 
     * @param int $warehouseId
     * @param array $locations ([$productId => $location])
     */
    public function updateLocation($warehouseId, $locations)
    {
        /* start queries processing */
        $this->_queryProcessor->start();

        /* prepare to update shelf location in Warehouse, then add queries to Processor */
        $this->_prepareUpdateLocation($warehouseId, $locations);

        /* process queries in Processor */
        $this->_queryProcessor->process();        
    }


    public function forceEditAvailableQty($warehouseId,$availQty){

        if(!count($availQty)) {
            return $this;
        }
        $connection = $this->getConnection();
        $conditions = [];
        foreach($availQty as $productId => $qty) {
            $case = $connection->quoteInto('?', $productId);
            $conditions[$case] = $connection->quoteInto('?', $qty);
        }
        $values = ['qty' => $connection->getCaseSql('product_id', $conditions, 'qty')];
        $where = ['product_id IN (?)' => array_keys($availQty), ProductInterface::WAREHOUSE_ID.' = ?' => $warehouseId];

        /* add query to the processor */
        $query = [
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => $values,
            'condition' => $where,
            'table' => $this->getTable(WarehouseProductResource::MAIN_TABLE)
        ];
        /* start queries processing */
        $this->_queryProcessor->start();
        $this->_queryProcessor->addQuery($query);
        /* process queries in Processor */
        $this->_queryProcessor->process();
    }

    /**
     * prepare to update shelf location in Warehouse
     * add queries to Processor
     * 
     * @param int $warehouseId
     * @param array $locations
     */
    protected function _prepareUpdateLocation($warehouseId, $locations)
    {
        if(!count($locations)) {
            return $this;
        }
        $connection = $this->getConnection();
        $conditions = [];
        foreach($locations as $productId => $location) {
             $case = $connection->quoteInto('?', $productId);
             $conditions[$case] = $connection->quoteInto('?', $location);
        }
        $values = ['shelf_location' => $connection->getCaseSql('product_id', $conditions, 'shelf_location')];
        $where = ['product_id IN (?)' => array_keys($locations), ProductInterface::WAREHOUSE_ID.' = ?' => $warehouseId];
        
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
     * Prepare query to add product to warehouse
     * Do not update global stock
     * Do not commit query
     * 
     * @param int $warehouseId
     * @param int $productId
     * @param array $stockData
     */
    public function prepareAddProductToWarehouse($warehouseId, $productId, $stockData)
    {
        $stockData[ProductInterface::TOTAL_QTY] = isset($stockData[ProductInterface::TOTAL_QTY]) ? ($stockData[ProductInterface::TOTAL_QTY]) : 0;
        $stockData[ProductInterface::AVAILABLE_QTY] = isset($stockData[ProductInterface::AVAILABLE_QTY]) ? ($stockData[ProductInterface::AVAILABLE_QTY]) : 0;
        $stockData[ProductInterface::SHELF_LOCATION] = isset($stockData[ProductInterface::SHELF_LOCATION]) ? ($stockData[ProductInterface::SHELF_LOCATION]) : '';
        $stockData[ProductInterface::WAREHOUSE_ID] = $warehouseId; 
        $stockData[ProductInterface::PRODUCT_ID] = $productId; 
        $stockData[ProductInterface::STOCK_ID] = $warehouseId; 
        /* validate total_qty & qty_to_ship */
        $stockData[ProductInterface::TOTAL_QTY] = max(0, $stockData[ProductInterface::TOTAL_QTY]);
        $stockData[ProductInterface::AVAILABLE_QTY] = max(0, $stockData[ProductInterface::AVAILABLE_QTY]);  
        if(isset($stockData[ProductInterface::QTY_TO_SHIP])) {
            unset($stockData[ProductInterface::QTY_TO_SHIP]);
        }
        $query = [
            'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
            'values' => [$stockData],
            'table' => $this->getTable(WarehouseProductResource::MAIN_TABLE)            
        ];
        return $query;
    }    
    
    /**
     * Prepare query to change total_qty, qty_to_ship of product in warehouse
     * Do not update global stock
     * Do not commit query
     * 
     * @param int $warehouseId
     * @param int $productId
     * @param array $changeQtys
     */
    public function prepareChangeProductQty($warehouseId, $productId, $changeQtys)
    {
        if(!count($changeQtys))
            return [];
        
        $values = [];
        foreach($changeQtys as $field => $qtyChange) {
            $operation = $qtyChange > 0 ? '+' : '-';
            if($field == ProductInterface::QTY_TO_SHIP) {
                $operation = $qtyChange < 0 ? '+' : '-';
                $field = ProductInterface::AVAILABLE_QTY;
            }
            $values[$field] = new \Zend_Db_Expr($field.$operation.abs($qtyChange));
        }
        $where = ['product_id=?' => $productId, ProductInterface::WAREHOUSE_ID .'=?' => $warehouseId];

        $query = [
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => $values,
            'condition' => $where,
            'table' => $this->getTable(WarehouseProductResource::MAIN_TABLE)            
        ];
        return $query;
    }
    
    /**
     * Prepare query to mass change total_qty, qty_to_ship of products in warehouse
     * Do not update global stock
     * Do not commit query
     * $changeQtys[$warehouseId => [$productId => ['total_qty' => 2]]]
     * 
     * @param array $changeQtys
     * @return array
     */
    public function prepareMassChangeProductQty($changeQtys)
    {
        /* update later */
        return [];
    }   
    
    /**
     * clone stock item data to many warehouses
     * 
     * @param int $productId
     * @param array $stockItemData
     * @param array $warehouses
     * @param array $ignoreWarehouses
     */
    public function cloneStockItemData($productId, $stockItemData,  $warehouses = array(), $ignoreWarehouses = array())
    {
        $conditions = array(
            ProductInterface::PRODUCT_ID .'=?' => $productId
        );
        if(count($ignoreWarehouses)) {
           $conditions[] = ProductInterface::WAREHOUSE_ID.' NOT IN ('. implode($ignoreWarehouses) .')';
        }
        if(count($warehouses)) {
           $conditions[] = ProductInterface::WAREHOUSE_ID.' IN ('. implode($warehouses) .')';
        }       
        $query = array(
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => $stockItemData,
            'condition' => $conditions,
            'table' => $this->getTable('cataloginventory_stock_item')           
        );
        $this->_queryProcessor->start('clone_stock_item');
        $this->_queryProcessor->addQuery($query, 'clone_stock_item');
        $this->_queryProcessor->process('clone_stock_item');
    }    
    
    /**
     * clone stock status to many warehouses
     * 
     * @param int $productId
     * @param array $stockStatusData
     * @param array $warehouses
     * @param array $ignoreWarehouses
     */
    public function cloneStockStatus($productId, $stockStatusData,  $warehouses = array(), $ignoreWarehouses = array())
    {
        $conditions = array(
            ProductInterface::PRODUCT_ID .'=?' => $productId
        );
        if(count($ignoreWarehouses)) {
           $conditions[] = ProductInterface::WAREHOUSE_ID.' NOT IN ('. implode($ignoreWarehouses) .')';
        }
        if(count($warehouses)) {
           $conditions[] = ProductInterface::WAREHOUSE_ID.' IN ('. implode($warehouses) .')';
        }       
        $query = array(
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => $stockStatusData,
            'condition' => $conditions,
            'table' => $this->getTable('cataloginventory_stock_status')           
        );
        $this->_queryProcessor->start('clone_stock_status');
        $this->_queryProcessor->addQuery($query, 'clone_stock_status');
        $this->_queryProcessor->process('clone_stock_status');     
    }     

}