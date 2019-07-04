<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Warehouse;

interface OrderItemManagementInterface
{
    /**
     * Get WarehouseId by orderId
     * 
     * @param int $orderId
     * @return int
     */
    public function getWarehouseByOrderId($orderId);
    
    /**
     * Get WarehouseId by order item Id
     * 
     * @param int $itemId
     * @return int
     */
    public function getWarehouseByItemId($itemId);
    
    /**
     * Get WarehouseIds by orderitemIds
     * 
     * @param array $itemIds
     * @return array
     */
    public function getWarehousesByItemIds($itemIds);    
    
    /**
     * Check existed orderItem in Warehouse
     * 
     * @param int $itemId
     * @return true
     */
    public function isExisted($itemId);
    
    /**
     * Prepare query to change total_qty, qty_to_ship of orderItem in warehouse
     * Do not commit query
     * 
     * @param int $warehouseId
     * @param int $itemId
     * @param array $changeQtys
     * @return array
     */
    /*
    public function prepareChangeItemQty($warehouseId, $itemId, $changeQtys);   
    */ 
}          