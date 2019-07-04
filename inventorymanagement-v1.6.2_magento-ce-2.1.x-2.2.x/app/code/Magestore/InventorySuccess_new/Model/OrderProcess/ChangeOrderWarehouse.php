<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\OrderProcess;

use Magestore\InventorySuccess\Api\OrderProcess\ChangeOrderWarehouseInterface;
use Magestore\InventorySuccess\Api\Db\QueryProcessorInterface;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;

class ChangeOrderWarehouse extends OrderProcess implements ChangeOrderWarehouseInterface
{
    /**
     * @var string
     */
    protected $process = 'change_order_warehouse';

    /**
     * execute the process
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface $warehouse
     * @return bool
     */
    public function execute($order, $warehouse)
    {
        if (!$this->canProcessOrder($order, $warehouse))
            return false;
        
        $this->queryProcess->start($this->process);

        $this->assignOrderToWarehouse($order, $warehouse);

        $this->changeOrderItemsWarehouse($order, $warehouse);

        $this->queryProcess->process($this->process);

        $this->markOrderProcessed($order, $warehouse);

        return true;
    }

    /**
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface $warehouse
     */
    public function assignOrderToWarehouse($order, $warehouse)
    {
        $this->queryProcess->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => ['warehouse_id' => $warehouse->getId()],
            'condition' => ['entity_id=?' => $order->getId()],
            'table' => $order->getResource()->getMainTable(),
        ], $this->process);


        $this->queryProcess->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => ['warehouse_id' => $warehouse->getId()],
            'condition' => ['entity_id=?' => $order->getId()],
            'table' => $order->getResource()->getTable('sales_order_grid'),
        ], $this->process);
    }

    /**
     * Change order items to new warehouse
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface $warehouse
     */
    public function changeOrderItemsWarehouse($order, $warehouse)
    {
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        foreach ($order->getItems() as $orderItem) {
            if ($this->canProcessItem($orderItem)) {
                if ($orderItem->getWarehouseId()) {
                    $this->_decreaseQtyToShipInOrderWarehouse($orderItem, $orderItem->getWarehouseId());
                }
                $this->assignOrderItemToWarehouse($orderItem, $warehouse);
            }
        }
    }

    /**
     * Assign order item to Warehouse
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface $warehouse
     */
    public function assignOrderItemToWarehouse($orderItem, $warehouse)
    {
        $this->_increaseQtyToShipInOrderWarehouse($orderItem, $warehouse);
        $this->_addWarehouseOrderItem($orderItem, $warehouse);
    }

    /**
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param int $warehouseId
     */
    protected function _decreaseQtyToShipInOrderWarehouse($orderItem, $warehouseId)
    {
        $qtyChanges = array(WarehouseProductInterface::QTY_TO_SHIP => -$this->_getQtyToShip($orderItem));
        $queries = $this->warehouseStockRegistry
            ->prepareChangeProductQty($warehouseId, $orderItem->getProductId(), $qtyChanges);
        foreach ($queries as $query) {
            $this->queryProcess->addQuery($query, $this->process);
        }
    }

    /**
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface $warehouse
     */
    protected function _increaseQtyToShipInOrderWarehouse($orderItem, $warehouse)
    {
        $qtyChanges = array(WarehouseProductInterface::QTY_TO_SHIP => $this->_getQtyToShip($orderItem));
        $queries = $this->warehouseStockRegistry
            ->prepareChangeProductQty($warehouse->getWarehouseId(), $orderItem->getProductId(), $qtyChanges);
        foreach ($queries as $query) {
            $this->queryProcess->addQuery($query, $this->process);
        }
    }

    /**
     *  Add new warehouse id for order item
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface $warehouse
     */
    protected function _addWarehouseOrderItem($orderItem, $warehouse)
    {
        $this->queryProcess->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => ['warehouse_id' => $warehouse->getId()],
            'condition' => ['item_id=?' => $orderItem->getId()],
            'table' => $orderItem->getResource()->getMainTable(),
        ], $this->process);
    }


    /**
     * Check order can be proccessed
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface $warehouse
     * @return bool
     */
    public function canProcessOrder($order, $warehouse)
    {
        if ($order->getWarehouseId() == $warehouse->getWarehouseId())
            return false;

        $key = $this->process . 'order' . $order->getId();
        if ($this->registry->registry($key))
            return false;

        return true;
    }

    /**
     * Mark order as proccessed
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface $warehouse
     */
    public function markOrderProcessed($order, $warehouse)
    {
        $order->setWarehouseId($warehouse->getWarehouseId());
        $key = $this->process . 'order' . $order->getId();
        if (!$this->registry->registry($key)) {
            $this->registry->register($key, true);
        }
    }

    /**
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return boolean
     */
    public function canProcessItem($orderItem)
    {
        /* check manage stock or not */
        if (!$this->isManageStock($orderItem))
            return false;
        return true;
    }

    /**
     * Get Qty to Ship of Item
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return float
     */
    protected function _getQtyToShip($orderItem)
    {
        if ($this->_isUsedParentItem($orderItem)) {
            return $orderItem->getParentItem()->getQtyToShip();
        }
        return $orderItem->getQtyToShip();
    }

    /**
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @return boolean
     */
    protected function _isUsedParentItem($orderItem)
    {
        if ($orderItem->getParentItemId()) {
            if ($orderItem->getParentItem()->getProductType() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
                return false;
            }
            return true;
        }
        return false;
    }
}