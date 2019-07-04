<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\OrderProcess;

interface PlaceNewOrderInterface
{
    /**
     * execute the process
     * 
     * @param \Magento\Sales\Model\Order\Item $item
     * @param \Magento\Sales\Model\Order\Item $itemBefore
     * @return bool
     */
    public function execute($item, $itemBefore);
    
    /**
     * Get warehouse which responds to the order
     * 
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface
     */
    public function getOrderWarehouse($order);
    
    /**
     * 
     * @param \Magento\Sales\Model\Order\Item $orderItem
     */
    public function assignOrderItemToWarehouse($orderItem);
}