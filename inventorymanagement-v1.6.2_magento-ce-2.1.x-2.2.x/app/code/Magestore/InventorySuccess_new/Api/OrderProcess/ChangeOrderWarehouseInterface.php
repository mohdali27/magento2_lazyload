<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\OrderProcess;

interface ChangeOrderWarehouseInterface
{
    /**
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface $warehouse
     * @return bool
     */
    public function execute($order, $warehouse);

    /**
     * Assign order item to Warehouse
     *
     * @param \Magento\Sales\Api\Data\OrderItemInterface $orderItem
     * @param \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface $warehouse
     */
    public function assignOrderItemToWarehouse($orderItem, $warehouse);
    
    /**
     * 
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface $warehouse
     */
    public function assignOrderToWarehouse($order, $warehouse);
}