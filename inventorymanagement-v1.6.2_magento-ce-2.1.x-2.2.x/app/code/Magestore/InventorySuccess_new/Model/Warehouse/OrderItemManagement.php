<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Warehouse;

use Magestore\InventorySuccess\Api\Warehouse\OrderItemManagementInterface;

class OrderItemManagement implements OrderItemManagementInterface
{
    /**
     * @var \Magestore\InventorySuccess\Model\Warehouse\Order\ItemFactory
     */
    protected $warehouseOrderItemFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $orderItemCollectionFactory;


    public function __construct(
        \Magestore\InventorySuccess\Model\Warehouse\Order\ItemFactory $warehouseOrderItemFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory
    )
    {
        $this->warehouseOrderItemFactory = $warehouseOrderItemFactory;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
    }

    /**
     * Get WarehouseId by order item Id
     *
     * @param int $itemId
     * @return null|int
     */
    public function getWarehouseByItemId($itemId)
    {
        $warehouseItem = $this->orderItemCollectionFactory->create()
                    ->addFieldToFilter('item_id', $itemId)
                    ->setPageSize(1)->setCurPage(1)
                    ->getFirstItem();
        return $warehouseItem->getWarehouseId();
    }

    /**
     * Get WarehouseId by orderId
     *
     * @param int $orderId
     * @return null|int
     */
    public function getWarehouseByOrderId($orderId)
    {
        $warehouseItem = $this->orderItemCollectionFactory->create()
                    ->addFieldToFilter('order_id', $orderId)
                    ->setPageSize(1)->setCurPage(1)
                    ->getFirstItem();
        return $warehouseItem->getWarehouseId();
    }

    /**
     * Get WarehouseIds by orderitemIds
     *
     * @param array $itemIds
     * @return array
     */
    public function getWarehousesByItemIds($itemIds)
    {
        $warehouseIds = [];
        $collection = $this->orderItemCollectionFactory->create()
                    ->addFieldToSelect(['item_id', 'warehouse_id'])
                    ->addFieldToFilter('item_id', ['in' => $itemIds]);
        if($collection->getSize()) {
            foreach($collection as $item) {
                $warehouseIds[$item->getItemId()] = $item->getWarehouseId();
            }
        }
        return $warehouseIds;
    }

    /**
     * Check existed orderItem in Warehouse
     *
     * @param int $itemId
     * @return true
     */
    public function isExisted($itemId)
    {
        $collection = $this->orderItemCollectionFactory->create()
                        ->addFieldToFilter('item_id', $itemId);
        if($collection->getSize())
            return true;
        return false;
    }

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
    public function prepareChangeItemQty($warehouseId, $itemId, $changeQtys)
    {
        return $this->getResource()->prepareChangeItemQty($warehouseId, $itemId, $changeQtys);
    }
    */

    /**
     * Get resource model
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\OrderItemManagement
     */
    /*
    public function getResource()
    {
        return $this->resourceFactory->create();
    }    
    */
}