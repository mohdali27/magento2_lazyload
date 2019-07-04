<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\OrderProcess;

use Magestore\InventorySuccess\Api\OrderProcess\PlaceNewOrderInterface;
use Magestore\InventorySuccess\Api\Db\QueryProcessorInterface;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;

class PlaceNewOrder extends OrderProcess implements PlaceNewOrderInterface
{
    /**
     * @var string
     */
    protected $process = 'place_new_order';

    /**
     * @var array
     */
    private $orderWarehouses = [];

    /**
     * execute the process
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param \Magento\Sales\Model\Order\Item $itemBefore
     * @return bool
     */
    public function execute($item, $itemBefore)
    {
        if (!$this->canProcessItem($item, $itemBefore)) {
            return;
        }

        $this->assignOrderItemToWarehouse($item);

        $this->markItemProcessed($item);

        return true;
    }

    /**
     * Assign order item to Warehouse
     *
     * @param \Magento\Sales\Model\Order\Item $item
     */
    public function assignOrderItemToWarehouse($item)
    {
        $this->queryProcess->start($this->process);

        $this->_addWarehouseOrderItem($item);
        $this->_increaseQtyToShipInOrderWarehouse($item);

        $this->queryProcess->process($this->process);

    }

    /**
     *
     * @param \Magento\Sales\Model\Order\Item $item
     */
    protected function _addWarehouseOrderItem($item)
    {
        $warehouse = $this->getOrderWarehouse($item->getOrder(),$item);
        $this->queryProcess->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => ['warehouse_id' => $warehouse->getId()],
            'condition' => ['item_id=?' => $item->getId()],
            'table' => $item->getResource()->getMainTable(),
        ], $this->process);

        /*
        $warehouseOrderModel = $this->warehouseOrderItemFactory->create();
        $warehouseOrderData = [
            'warehouse_id' => $warehouse->getId(),
            'order_id' => $item->getOrderId(),
            'item_id' => $item->getId(),
            'product_id' => $item->getProductId(),
            'qty_ordered' => $this->_getOrderedQty($item),
            'subtotal' => $item->getRowTotal(),
            'created_at' => $item->getOrder()->getCreatedAt(),
            'updated_at' => $item->getOrder()->getUpdatedAt(),
        ];
        $this->queryProcess->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
            'values' => [$warehouseOrderData],
            'table' => $warehouseOrderModel->getResource()->getMainTable(),
        ]);
        */
    }

    /**
     *
     * @param \Magento\Sales\Model\Order\Item $item
     */
    protected function _increaseQtyToShipInOrderWarehouse($item)
    {
        $orderWarehouse = $this->getOrderWarehouse($item->getOrder());
        if ($this->catalogInventoryConfiguration->getDefaultScopeId() == WarehouseProductInterface::DEFAULT_SCOPE_ID) {
            /* place order from global stock */
            $updateWarehouseId = $orderWarehouse->getWarehouseId();
        } else {
            /* place order from warehouse stock  */
            $updateWarehouseId = WarehouseProductInterface::DEFAULT_SCOPE_ID;
        }

        $qtyChanges = array(WarehouseProductInterface::QTY_TO_SHIP => $this->_getOrderedQty($item));
        $queries = $this->warehouseStockRegistry->prepareChangeProductQty($updateWarehouseId, $item->getProductId(), $qtyChanges);
        foreach ($queries as $query) {
            $this->queryProcess->addQuery($query, $this->process);
        }
    }


    /**
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param \Magento\Sales\Model\Order\Item $itemBefore
     * @return boolean
     */
    public function canProcessItem($item, $itemBefore)
    {
        /* check new item */
        if ($itemBefore->getId()) {
            return false;
        }

        /* check processed item */
        if ($this->isProcessedItem($item)) {
            return false;
        }

        /* check manage stock or not */
        if (!$this->isManageStock($item)) {
            return false;
        }

        if ($item->getWarehouseId()) {
            return false;
        }

        return true;
    }

    /**
     * Get warehouse which responds to the order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface
     */
    public function getOrderWarehouse($order,$item = null)
    {
        /* integration with m2e-listings */
        $product_id = 0;
        if($item){
            $product_id = $item->getProductId();
        }

        if (!isset($this->orderWarehouses[$order->getId()])) {
            if ($this->inventoryHelper->getLinkWarehouseStoreConfig()) {
                $orderWarehouse = $this->warehouseManagement->getCurrentWarehouseByStore();
            }
            if (!isset($orderWarehouse) || !$orderWarehouse->getId()) {
                $orderWarehouse = $this->warehouseManagement->getPrimaryWarehouse();
            }
            /* allow to change the order Warehouse by other extension */
            $this->eventManager->dispatch('inventorysuccess_new_order_warehouse', [
                'order' => $order,
                'warehouse' => $orderWarehouse,
                'product_id' => $product_id
            ]);
            $this->orderWarehouses[$order->getId()] = $orderWarehouse;
        }
        return $this->orderWarehouses[$order->getId()];
    }

    public function isProductInWarehouse($productId, $warehouseId)
    {

    }
}

