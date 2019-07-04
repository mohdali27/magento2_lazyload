<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\StockActivity;


interface StockChangeInterface
{
    
    /**
     * Define qty actions
     */
    CONST QTY_CHANGE_ACTION = 'change';
    CONST QTY_UPDATE_ACTION = 'update';
    CONST QTY_INCREASE_ACTION = 'increase';
    CONST QTY_DECREASE_ACTION = 'decrease';
    
    /*
     * Define stock activity
     */
    CONST ISSUE_STOCK = 'issue_stock';
    CONST RECEIVE_STOCK = 'receive_stock';
    CONST ADJUST_STOCK = 'adjust_stock';
    
    /**
     * Change qty of product in Warehouse
     * 
     * @param int $warehouseId
     * @param int $productId
     * @param float $qtyChange
     * @param bool $updateCatalog
     * @return StockChangeInterface
     */
    public function change($warehouseId, $productId, $qtyChange, $updateCatalog = true);
    
    /**
     * Change qty of products in Warehouse
     * 
     * @param int $warehouseId
     * @param array $qtys
     * @param bool $updateCatalog
     * @return StockChangeInterface
     */
    public function massChange($warehouseId, $qtys, $updateCatalog = true);
    
    /**
     * Update qty of product in Warehouse
     * 
     * @param int $warehouseId
     * @param int $productId
     * @param float $qtyUpdate
     * @param bool $updateCatalog
     * @return StockChangeInterface
     */
    public function update($warehouseId, $productId, $qtyUpdate, $updateCatalog = true);
    
    /**
     * Update qty of products in Warehouse
     * 
     * @param int $warehouseId
     * @param array $qtys
     * @param bool $updateCatalog
     * @return StockChangeInterface
     */
    public function massUpdate($warehouseId, $qtys, $updateCatalog = true);    
    
    /**
     * Increase qty of product in Warehouse
     * 
     * @param int $warehouseId
     * @param int $productId
     * @param float $qty
     * @param bool $updateCatalog
     * @return StockChangeInterface
     */
    public function increase($warehouseId, $productId, $qty, $updateCatalog = true);
    
    
    /**
     * Decrease qty of product in Warehouse
     * 
     * @param int $warehouseId
     * @param int $productId
     * @param float $qty
     * @param bool $updateCatalog
     * @return StockChangeInterface
     */
    public function decrease($warehouseId, $productId, $qty, $updateCatalog = true);   
    
    
    /**
     * Receive stocks to the warehouse
     * 
     * @param int $warehouseId
     * @param array $products
     * @param bool $updateCatalog
     * @param string $actionType
     * @param string $actionNumber
     * @param int $actionId
     * @return StockChangeInterface
     */
    public function receive($warehouseId, $products, $actionType, $actionId, $updateCatalog = true);
    
    /**
     * Issue stocks from the warehouse
     * 
     * @param int $warehouseId
     * @param array $products
     * @param bool $updateCatalog
     * @param string $actionType
     * @param string $actionNumber
     * @param int $actionId
     * @return StockChangeInterface
     */
    public function issue($warehouseId, $products, $actionType, $actionId, $updateCatalog = true);    
    
    /**
     * Adjust stocks in the warehouse
     * 
     * @param int $warehouseId
     * @param array $products
     * @param bool $updateCatalog
     * @param string $adjustmentNumber
     * @param int $adjustmentId
     */
    public function adjust($warehouseId, $products, $actionType, $actionId, $updateCatalog = true);       
    
}