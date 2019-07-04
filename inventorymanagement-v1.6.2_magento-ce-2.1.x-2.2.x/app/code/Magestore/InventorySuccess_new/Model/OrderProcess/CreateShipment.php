<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\OrderProcess;

use Magestore\InventorySuccess\Api\OrderProcess\CreateShipmentInterface;
use Magestore\InventorySuccess\Api\Db\QueryProcessorInterface;
use Magestore\InventorySuccess\Model\OrderProcess\StockMovementActivity\SalesShipment as StockActivitySalesShipment;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product as WarehouseProductResource;

class CreateShipment extends OrderProcess implements CreateShipmentInterface
{
    /**
     * @var string
     */
    protected $process = 'create_shipment';

    /**
     * @var array
     */
    protected $shipWarehouses = [];

    /**
     * @array
     */
    protected $simpleOrderItems = [];

    /**
     * execute the process
     *
     * @param \Magento\Sales\Model\Order\Shipment\Item $item
     * @return bool
     */
    public function execute($item)
    {
        if (!$this->canProcessItem($item)) {
            return;
        }

        $this->processShipItem($item);

        $this->markItemProcessed($item);

        return true;
    }

    /**
     * Process to ship item from Warehouse
     *
     * @param \Magento\Sales\Model\Order\Shipment\Item $item
     */
    public function processShipItem($item)
    {
        $this->queryProcess->start($this->process);

        /* add shipped item to the Warehouse */
        $this->_addWarehouseShipmentItem($item);

        /* subtract qty_to_ship in ordered Warehouse by shipped qty*/
        if ($item->getOrderItem()->getProductType() == \Magento\Bundle\Model\Product\Type::TYPE_CODE && $item->getOrderItem()->getProduct()->getData('shipment_type') == 0){
            $this->_subtractQtyToShipInOrderWarehouseForBundleProduct($item);
        } else {
            $this->_subtractQtyToShipInOrderWarehouse($item);
        }
        $this->queryProcess->process($this->process);

        /* issue ship item from Warehouse */
        $this->_issueItemFromWarehouse($item);

    }

    /**
     * Add shipment item to Warehouse
     *
     * @param \Magento\Sales\Model\Order\Shipment\Item $item
     */
    protected function _addWarehouseShipmentItem($item)
    {
        /** @var \Magento\Sales\Model\Order\Item $simpleItem */
//        $simpleItem = $this->_getSimpleOrderItem($item);
        $shipWarehouse = $this->getShipmentWarehouse($item);
        if (!$shipWarehouse)
            return $this;
        
        $this->queryProcess->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' =>  ['warehouse_id' => $shipWarehouse->getId()], 
            'condition' => ['entity_id=?' => $item->getId()],
            'table' => $item->getResource()->getMainTable(),
        ], $this->process);     
        
        /*
        $warehouseShipModel = $this->warehouseShipmentItemFactory->create();
        $warehouseShipData = [
            'warehouse_id' => $shipWarehouse->getId(),
            'shipment_id' => $item->getParentId(),
            'item_id' => $item->getId(),
            'order_id' => $item->getOrderItem()->getOrderId(),
            'order_item_id' => $item->getOrderItemId(),
            'product_id' => $simpleItem->getProductId(),
            'qty_shipped' => $this->_getShippedQty($item),
            'subtotal' => $item->getPrice(),
            'created_at' => $item->getShipment()->getCreatedAt(),
            'updated_at' => $item->getShipment()->getUpdatedAt(),
        ];
        $this->queryProcess->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
            'values' => [$warehouseShipData],
            'table' => $warehouseShipModel->getResource()->getMainTable(),
        ], 'shipment');
        */
    }

    /**
     * Get simple item from ship item
     *
     * @param \Magento\Sales\Model\Order\Shipment\Item $shipItem
     * @return \Magento\Sales\Model\Order\Item
     */
    protected function _getSimpleOrderItem($shipItem)
    {
        if (!isset($this->simpleOrderItems[$shipItem->getId()])) {
            $simpleItem = $shipItem->getOrderItem();
            $orderItem = $shipItem->getOrderItem();
            if ($orderItem->getProduct()->isComposite()) {
                if ($orderItem->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                    foreach ($orderItem->getChildrenItems() as $childItem) {
                        $simpleItem = $childItem;
                        break;
                    }
                }
            }

            $this->simpleOrderItems[$shipItem->getId()] = $simpleItem;
        }
        return $this->simpleOrderItems[$shipItem->getId()];
    }

    /**
     * subtract qty_to_ship of product in ordered warehouse
     *
     * @param \Magento\Sales\Model\Order\Shipment\Item $item
     */
    protected function _subtractQtyToShipInOrderWarehouse($item)
    {
        if (!$this->isManageStock($item->getOrderItem()))
            return $this;
        $orderItem = $this->_getSimpleOrderItem($item);
        $orderWarehouseId = $this->getOrderWarehouse($orderItem->getItemId());
        $qtyChanges = [WarehouseProductInterface::QTY_TO_SHIP => -$this->_getShippedQty($item)];
        $this->_updateQtyProcess($item, $orderItem, $orderWarehouseId, $qtyChanges);
    }

    /***
     * @param \Magento\Sales\Model\Order\Shipment\Item $item
     */
    protected function _subtractQtyToShipInOrderWarehouseForBundleProduct($item){
        $orderItems = $this->_getSimpleOrderItems($item);
        $shipWarehouse = $this->getShipmentWarehouse($item);
        $shippedWarehouseId = $shipWarehouse->getWarehouseId();
        foreach ($orderItems as $orderItem){
            if ($orderItem->getWarehouseId()) {
                if ($orderItem->getWarehouseId() == $shippedWarehouseId) {
                    $qtyChanges = [WarehouseProductInterface::TOTAL_QTY => $this->_getBundleChildItemShippedQty($item, $orderItem)];
                    $this->_updateQtyProcess($item, $orderItem, $shippedWarehouseId, $qtyChanges);
                } else {
                    $qtyChanges = [
                        WarehouseProductInterface::TOTAL_QTY => $this->_getBundleChildItemShippedQty($item, $orderItem)
                    ];
                    $this->_updateQtyProcessNotSameWarehouse($orderItem, $orderItem->getWarehouseId(), $shippedWarehouseId, $qtyChanges);
                }
            }
        }
    }

    /**
     * @param \Magento\Sales\Model\Order\Shipment\Item $item
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param int $orderWarehouseId
     * @param array $qtyChanges
     */
    protected function _updateQtyProcess($item, $orderItem, $orderWarehouseId, $qtyChanges){
        $dataEvent = new \Magento\Framework\DataObject(['is_increase_qty' => true]);
        $this->eventManager->dispatch('subtract_qty_to_ship_in_ordered_warehouse_before', ['data_event' => $dataEvent]);

        if($dataEvent->getData('is_increase_qty')) {
            /* increase available_qty in ordered warehouse  */
            $queries = $this->warehouseStockRegistry
                ->prepareChangeProductQty($orderWarehouseId, $orderItem->getProductId(), $qtyChanges);
            foreach ($queries as $query)
                $this->queryProcess->addQuery($query, $this->process);
        } else {
            // when run with fulfill
            // increase available qty on shipped warehouse
            $queries = $this->warehouseStockRegistry
                ->prepareChangeProductQty($this->getShipmentWarehouse($item)->getWarehouseId(), $orderItem->getProductId(), $qtyChanges);
            foreach ($queries as $query)
                $this->queryProcess->addQuery($query, $this->process);
        }

        /* increase available_qty in global stock */
        $queries = $this->warehouseStockRegistry
            ->prepareChangeProductQty(WarehouseProductInterface::DEFAULT_SCOPE_ID, $orderItem->getProductId(), $qtyChanges);
        foreach ($queries as $query)
            $this->queryProcess->addQuery($query, $this->process);
    }

    /**
     * @param \Magento\Sales\Model\Order\Shipment\Item $item
     * @param \Magento\Sales\Model\Order\Item $orderItem
     * @param int $orderWarehouseId
     * @param array $qtyChanges
     */
    protected function _updateQtyProcessNotSameWarehouse($orderItem, $orderWarehouseId, $shippedWarehouseId, $qtyChanges){
        /* increase available_qty in shipped warehouse */
        $newQtyChanges = $qtyChanges;
        $newQtyChanges[WarehouseProductInterface::AVAILABLE_QTY] = $qtyChanges[WarehouseProductInterface::TOTAL_QTY];
        $queries = $this->warehouseStockRegistry->prepareChangeProductQty($shippedWarehouseId, $orderItem->getProductId(), $newQtyChanges);
        foreach ($queries as $query) {
            $this->queryProcess->addQuery($query, $this->process);
        }

        /* increase available_qty in ordered warehouse */
        $newQtyChanges = [];
        $newQtyChanges[WarehouseProductInterface::AVAILABLE_QTY] = - $qtyChanges[WarehouseProductInterface::TOTAL_QTY];
        $queries = $this->warehouseStockRegistry->prepareChangeProductQty($orderWarehouseId, $orderItem->getProductId(), $newQtyChanges);
        foreach ($queries as $query) {
            $this->queryProcess->addQuery($query, $this->process);
        }
    }

    /**

     * @param \Magento\Sales\Model\Order\Shipment\Item[] $item
     */
    protected function _getSimpleOrderItems($shipItem){
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = $shipItem->getOrderItem();
        if ($orderItem->getProduct()->isComposite()) {
            if ($orderItem->getProductType() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
                return $orderItem->getChildrenItems();
            }
        }
        return [];
    }

    /**
     * @param \Magento\Sales\Model\Order\Shipment\Item $item
     * @param \Magento\Sales\Model\Order\Item $childItem
     */
    protected function _getBundleChildItemShippedQty($item, $childItem){
        $bundleShippedQty = $item->getQty();
        $bundleOrderedQty = $item->getOrderItem()->getQtyOrdered();
        $childItemQtyOrdered = $childItem->getQtyOrdered();
        return - $bundleShippedQty/$bundleOrderedQty * $childItemQtyOrdered;
    }

    /**
     * issue item from ship warehouse
     *
     * @param \Magento\Sales\Model\Order\Shipment\Item $item
     */
    protected function _issueItemFromWarehouse($item)
    {
        $orderItem = $this->_getSimpleOrderItem($item);
        if (!$this->getShipmentWarehouse($item))
            return $this;
        $shipWarehouseId = $this->getShipmentWarehouse($item)->getId();
        $products = [$orderItem->getProductId() => $this->_getShippedQty($item)];
        /* issue item for shipment from Warehouse, also update global stock */
        $this->stockChange->issue($shipWarehouseId, $products, StockActivitySalesShipment::STOCK_MOVEMENT_ACTION_CODE, $item->getShipment()->getId(), true);
        if (!$this->isManageStock($item->getOrderItem())) {
            $this->resetAllStockItems($item);
        }
    }

    /**
     *
     * @param \Magento\Sales\Model\Order\Shipment\Item $item
     * @return boolean
     */
    public function canProcessItem($item)
    {
        /* check processed item */
        if ($this->isProcessedItem($item)) {
            return false;
        }

//        /* check manage stock or not */
//        if (!$this->isManageStock($item->getOrderItem())) {
//            return false;
//        }

        $orderItem = $item->getOrderItem();
        if ($orderItem->getParentItem()
            && $orderItem->getParentItem()->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        ) {
            return false;
        }

        /* check added item */
        if ($item->getWarehouseId()) {
            return false;
        }

        return true;
    }

    /**
     * Get warehouse to ship item
     *
     * @param \Magento\Sales\Model\Order\Shipment\Item $item
     * @return \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface
     */
    public function getShipmentWarehouse($item)
    {
        if (!isset($this->shipWarehouses[$item->getId()])) {
            /* get posted warehouse_id */
            $postData = $this->request->getParam('shipment');
            $shipWarehouseId = isset($postData['warehouse']) ? $postData['warehouse'] : null;
            /* get ordered warehouse_id */
            $orderItem = $this->_getSimpleOrderItem($item);
            $shipWarehouseId = $shipWarehouseId
                ? $shipWarehouseId
                : $this->getOrderWarehouse($orderItem->getItemId());
            /* get primary warehouse_id */
            if (!$shipWarehouseId) {
                $shipWarehouse = $this->warehouseManagement->getPrimaryWarehouse();
            } else {
                $shipWarehouse = $this->warehouseFactory->create()->load($shipWarehouseId);
            }
            $skipWarehouse = false;
            /* allow to change the Warehouse by other extension */
            $this->eventManager->dispatch('inventorysuccess_create_shipment_warehouse', [
                'warehouse' => $shipWarehouse,
                'item' => $item,
                'order_id' => $item->getShipment()->getOrderId(),
                'skip_warehouse' => &$skipWarehouse
            ]);
            if ($skipWarehouse)
                return null;
            $this->shipWarehouses[$item->getId()] = $shipWarehouse;
        }
        return $this->shipWarehouses[$item->getId()];
    }

    /**
     * Manage stock of product in this item or not
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     */
    public function isManageStock($item)
    {
        $scopeId = $this->catalogInventoryConfiguration->getDefaultScopeId();
        $stockItem = $this->stockRegistryProvider->getStockItem($item->getProductId(), $scopeId);
        /* do not manage stock of this product */
        if(!$stockItem->getUseConfigManageStock() && !$stockItem->getManageStock()) {
            return false;
        }
        if($stockItem->getUseConfigManageStock() && !$this->catalogInventoryConfiguration->getManageStock()) {
            return false;
        }
        return true;
    }

    /**
     * Get warehouse to ship item
     *
     * @param \Magento\Sales\Model\Order\Shipment\Item $item
     * @return boolean
     */
    protected function resetAllStockItems($item)
    {
        try {
            $product = $this->productRepository->get($item->getSku());
            if ($product->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
                $this->queryProcess->start();
                $query = [
                    'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
                    'values' => ['qty' => 0, 'total_qty' => 0],
                    'condition' => ['product_id=?' => $product->getId()],
                    'table' => $this->warehouseStockRegistry->getResource()->getTable(WarehouseProductResource::MAIN_TABLE)
                ];
                $this->queryProcess->addQuery($query);
                $this->queryProcess->process();
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

}
