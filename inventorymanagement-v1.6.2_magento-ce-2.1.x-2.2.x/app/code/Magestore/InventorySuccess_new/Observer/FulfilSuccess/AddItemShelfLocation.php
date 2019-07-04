<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\FulfilSuccess;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product as WarehouseProductResource;

class AddItemShelfLocation implements ObserverInterface 
{
    /**
     * Add shelf location data to items
     * 
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer) 
    {
        $itemCollection = $observer->getEvent()->getPickItemCollection();
        $warehouseId = $observer->getEvent()->getWarehouseId();
        $itemCollection->getSelect()
                        ->join(['whProduct' => $itemCollection->getResource()->getTable(WarehouseProductResource::MAIN_TABLE)],
                                "main_table.product_id = whProduct.product_id AND whProduct.".WarehouseProductInterface::WAREHOUSE_ID." = '$warehouseId'",
                                 ['shelf_location']);
        return $this;
    }    
}