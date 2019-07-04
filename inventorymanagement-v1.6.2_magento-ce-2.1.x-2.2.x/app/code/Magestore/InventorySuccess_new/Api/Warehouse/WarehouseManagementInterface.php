<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Warehouse;

interface WarehouseManagementInterface
{
    
    const BEFORE_SUBTRACT_SALES_QTY = 'inventorysuccess_before_subtract_sales_qty';
    
    /**
     * Gets list product for a specified warehouse.
     *
     * @param $warehouseId
     * @return \Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface[]
     */
    public function getListProduct($warehouseId);

    /**
     * Gets stock movements for a specified warehouse.
     *
     * @param $warehouseId
     * @return \Magestore\InventorySuccess\Api\Data\StockMovement\StockMovementInterface[]
     */
    public function getStockMovement($warehouseId);
    
    /**
     * get primary warehouse
     * 
     * @return \Magestore\InventorySuccess\Model\Warehouse
     */
    public function getPrimaryWarehouse();
    
    /**
     * get Warehouses by Ids
     * 
     * @param array $ids
     * @return array
     */
    public function getWarehouses($ids);
    
    /**
     * get enable warehouses
     * 
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection
     */    
    public function getEnableWarehouses();
    
    /**
     * get disable warehouses
     * 
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection
     */       
    public function getDisableWarehouses();

    /**
     * 
     * @return \Magestore\InventorySuccess\Model\Warehouse
     */
    public function getCurrentWarehouseByStore();

    /**
     * @param string $storeId
     * @return \Magestore\InventorySuccess\Model\Warehouse
     */
    public function getWarehouseByStoreId($storeId);
    
    /**
     * 
     * @return bool
     */
    public function isGetStockFromWarehouse();

}          