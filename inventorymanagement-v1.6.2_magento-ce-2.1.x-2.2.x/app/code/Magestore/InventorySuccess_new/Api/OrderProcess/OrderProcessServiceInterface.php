<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\OrderProcess;

use Magestore\InventorySuccess\Model\Warehouse;

interface OrderProcessServiceInterface
{
    const CHANGE_WAREHOUSE_PERMISSION = 'Magestore_InventorySuccess::change_order_warehouse';
    const VIEW_ORDER_WAREHOUSE_PERMISSION = 'Magestore_InventorySuccess::view_order_warehouse';
    const CREATE_SHIPMENT_WAREHOUSE_PERMISSION = 'Magestore_InventorySuccess::create_shipment';
    const CREATE_CREDITMEMO_WAREHOUSE_PERMISSION = 'Magestore_InventorySuccess::create_creditmemo';
    const CANCEL_ORDER_WAREHOUSE_PERMISSION = 'Magestore_InventorySuccess::cancel_order';

    /**
     * Check current user is allowed to change warehouse for order
     *
     * @return bool|mixed
     */
    public function canChangeOrderWarehouse();

    /**
     * Check current user is allow to view this warehouse order
     *
     * @param Warehouse|null $warehouse
     * @return bool|mixed
     */
    public function canViewWarehouse(Warehouse $warehouse = null);

    /**
     * Get List warehouse are allowed to view by current user
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection
     */
    public function getViewWarehouseList();

    /**
     * Get List warehouse are allowed to create shipment by current user
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection
     */
    public function getShipmentWarehouseList();
    
    /**
     * Check current user is allowed to create shipment for warehouse
     *
     * @param Warehouse|null $warehouse
     * @return bool|mixed
     */
    public function canCreateShipment(Warehouse $warehouse = null);

    /**
     * Get List warehouse are allowed to create credit memo by current user
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection
     */
    public function getCreditmemoWarehouseList();
}