<?php


/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Warehouse;

interface WarehouseStockRegistryInterface
{
    /**
     * get Stocks from Warehouse
     * 
     * @param int $warehouseId
     * @param array $productIds
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection
     */
    public function getStocks($warehouseId, $productIds = []);
    
    /**
     * get Stocks from enable Warehouses
     * 
     * @param array $productIds
     * @param array $warehouseIds
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection
     */    
    public function getStocksFromEnableWarehouses($productIds = [], $warehouseIds = []);
    
    /**
     * get Stock from Warehouse
     * 
     * @param int $warehouseId
     * @param int $productId
     * @return \Magestore\InventorySuccess\Model\Warehouse\Product
     */
    public function getStock($warehouseId, $productId);
    
    /**
     * remove Product from Warehouse
     * 
     * @param int $warehouseId
     * @param int $productId
     * @return array
     */
    public function removeProduct($warehouseId, $productId);

    /**
     * remove Products from Warehouse
     *
     * @param int $warehouseId
     * @param int $productId
     * @return array
     */
    public function removeProducts($warehouseId, $productIds);
    
    /**
     * Get list of warehouses which contains product
     * 
     * @param int $productId
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection
     */
    public function getStockWarehouses($productId);
    
    /**
     * Get list of warehouses which contains products
     * 
     * @param array $productIds
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection
     */    
    public function getStocksWarehouses($productIds);    
    
    /**
     * Update shelf location in Warehouse
     * 
     * @param int $warehouseId
     * @param array $locations
     * @return array
     */
    public function updateLocation($warehouseId, $locations);

    /**
     * Force edit available qty in Location
     *
     * @param int $warehouseId
     * @param array $availQty
     * @return array
     */
    public function forceEditAvailableQty($warehouseId,$availQty);
    /**
     * Prepare query to change total_qty, qty_to_ship of product in warehouse
     * Do not update global stock
     * Do not commit query
     * 
     * @param int $warehouseId
     * @param int $productId
     * @param array $changeQtys
     * @return array
     */
    public function prepareChangeProductQty($warehouseId, $productId, $changeQtys);
    
    
    /**
     * Prepare query to mass change total_qty, qty_to_ship of products in warehouse
     * Do not update global stock
     * Do not commit query
     * $changeQtys[$warehouseId => [$productId => ['total_qty' => 2]]]
     * 
     * @param array $changeQtys
     * @return array
     */
    public function prepareMassChangeProductQty($changeQtys);    
    
    /**
     * 
     * @param int $productId
     * @return \Magento\Framework\DataObject
     */
    public function getStoreDataFromCurrentStore($productId);   
    
    /**
     * 
     * @param int $productId
     * @param array $stockItemData
     * @param array $warehouses
     * @param array $ignoreWarehouses
     */
    public function cloneStockItemData($productId, $stockItemData, $warehouses=[], $ignoreWarehouses=[]);
    
    /**
     * 
     * @param int $productId
     * @param array $stockStatusData
     * @param array $warehouses
     * @param array $ignoreWarehouses
     */
    public function cloneStockStatus($productId, $stockStatusData, $warehouses=[], $ignoreWarehouses=[]);    
    
    /**
     * prepare to change qtys of product in multiple warehouses
     * 
     * @param int $productId
     * @param array $changeQtys
     * @return array
     */
    public function prepareChangeQtys($productId, $changeQtys);    

}


