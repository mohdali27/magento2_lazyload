<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api;


interface InstallManagementInterface
{
    
    /**
     * Create default Warehouse 
     * 
     * @return \Magestore\InventorySuccess\Api\InstallManagementInterface
     */
    public function createDefaultWarehouse();
    
    /**
     * Transfer all products to the default Warehouse
     * 
     * @return \Magestore\InventorySuccess\Api\InstallManagementInterface
     */
    public function transferProductsToDefaultWarehouse();
    
    /**
     * Calculate qty-to-ship of all products
     * 
     * @return \Magestore\InventorySuccess\Api\InstallManagementInterface
     */
    public function calculateQtyToShip();

    /**
     * create default notification rule
     *
     * @return \Magestore\InventorySuccess\Api\InstallManagementInterface
     */
    public function createDefaultNotificationRule();
    
    
    /**
     * transfer data in os_warehouse_product to cataloginventory_stock_item
     * use to upgrade Magestore_InventorySuccess from v1.0.0 to v1.1.0 and higher
     * 
     * @return \Magestore\InventorySuccess\Api\InstallManagementInterface
     */
    public function transferWarehouseProductToMagentoStockItem();

    /**
     * convert data from os_warehouse_order_item into sales_order_item
     * convert data from os_warehouse_shipment_item into sales_shipment_item
     * convert data from os_warehouse_creditmemo_item into sales_creditmemo_item
     *
     * @return \Magestore\InventorySuccess\Api\InstallManagementInterface
     */
    public function convertSaleItemsData();
    
    /**
     * Update stock id value base on website id in table cataloginventory_stock_item
     * Magento 2.2 update unique fields of table cataloginventory_stock_item 
     * from 'product_id' and 'website_id' to 'product_id' and 'stock_id'
     *
     * @return \Magestore\InventorySuccess\Api\InstallManagementInterface
     */
    public function updateStockId();

}