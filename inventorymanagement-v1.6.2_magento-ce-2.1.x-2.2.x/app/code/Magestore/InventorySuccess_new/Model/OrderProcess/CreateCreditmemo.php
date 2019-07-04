<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\OrderProcess;

use Magestore\InventorySuccess\Api\OrderProcess\CreateCreditmemoInterface;
use Magestore\InventorySuccess\Api\Db\QueryProcessorInterface;
use Magestore\InventorySuccess\Api\Data\AdjustStock\AdjustStockInterface;
use Magestore\InventorySuccess\Model\OrderProcess\StockMovementActivity\SalesRefund as StockActivitySalesRefund;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;

class CreateCreditmemo extends OrderProcess implements CreateCreditmemoInterface
{

    /**
     * @var string
     */
    protected $process = 'create_creditmemo';

    /**
     * @var array
     */
    protected $doReturnWarehouses = [];

    /**
     * execute the process
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item
     * @return bool
     */
    public function execute($item)
    {
        if(!$this->canProcessItem($item)){
            return;
        }

        $this->processRefundItem($item);

        $this->markItemProcessed($item);

        return true;
    }

    /**
     * Process to refund item
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item
     */
    public function processRefundItem($item)
    {
        if ($item->getBackToStock() || $this->catalogInventoryConfiguration->isAutoReturnEnabled()) {
            /* return item to warehouse */
            $this->_returnToWarehouse($item);
        } else {
            /* do not return item to warehouse */
            $this->_subtractItemInWarehouse($item);
        }
    }

    /**
     * Process return item to Warehouse
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item
     */
    protected function _returnToWarehouse($item)
    {
        if(!$this->_getRefundQty($item))
            return;

        $this->queryProcess->start($this->process);

        /* Add a new creditmemoItem record to return Warehouse */
        $this->_addWarehouseCreditmemoItem($item);

        /* Receive shipped item to returned Warehouse */
        $this->_receiveItemToReturnWarehouse($item);

        /* Update availablq_qty in warehouse stocks after returned */
        $this->_updateAvailableQtyAfterReturn($item);

        $this->queryProcess->process($this->process);
    }

    /**
     * Subtract item from warehouse if do not return item
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item
     */
    protected function _subtractItemInWarehouse($item)
    {
        if(!$this->_getRefundQty($item))
            return;

        $this->queryProcess->start($this->process);

        /* Add a new creditmemoItem record to return Warehouse */
        $this->_addWarehouseCreditmemoItem($item);

        /* Increase total_qty in ordered Warehouse by not_ship_qty */
        $this->_adjustStockInOrderWarehouse($item);

        /* Update availablq_qty in warehouse stocks after refunded without return */
        $this->_updateAvailableQtyAfterRefund($item);

        $this->queryProcess->process($this->process);
    }

    /**
     * Create new adjutsStock to decrease total_qty in ordered Warehouse
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item
     */
    protected function _adjustStockInOrderWarehouse($item)
    {
        $simpleItem = $this->_getSimpleItem($item->getOrderItem());
        $orderWarehouseId = $this->getOrderWarehouse($simpleItem->getItemId());
        /* prepare adjuststock data */
        $creditmemoData =  $this->request->getParam('creditmemo');
        $reason = isset($creditmemoData['items'][$item->getOrderItemId()]['reason']) ? $creditmemoData['items'][$item->getOrderItemId()]['reason'] : '';
        $warehouseStock = $this->warehouseStockRegistry->getStock($orderWarehouseId, $simpleItem->getProductId());
        $adjustData = [];
        $adjustQty = max(0, $warehouseStock->getTotalQty() - $this->_getNotShipQtyInRefund($item));
        $adjustData['products'] = [
            $simpleItem->getProductId() => [
                'adjust_qty' => $adjustQty,
                'product_name' => $simpleItem->getProduct()->getName(),
                'product_sku' => $simpleItem->getProduct()->getSku(),
                ]
            ];
        $adjustData[AdjustStockInterface::WAREHOUSE_ID] = $orderWarehouseId;
        $adjustData[AdjustStockInterface::REASON] = __('Do not return items to stock in Creditmemo #%1', $item->getCreditmemo()->getIncrementId())
                                                    . '. '. $reason;

        /* change stock adjustment data by other extension */
        $adjustDataObject = new \Magento\Framework\DataObject($adjustData);
        $this->eventManager->dispatch('inventorysuccess_create_creditmemo_adjuststock_data', [
                                        'adjuststock_data' => $adjustDataObject,
                                        'order' => $item->getOrderItem()->getOrder(),
                ]);
        $adjustData = $adjustDataObject->getData();

        /* create new stock adjustment (also update catalog qty), then complete it */
        $adjustStock = $this->adjustStockFactory->create();
        $this->adjustStockManagement->createAdjustment($adjustStock, $adjustData);
        $this->adjustStockManagement->complete($adjustStock, true);
    }

    /**
     * Add a new creditmemoItem record to return Warehouse
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item
     */
    protected function _addWarehouseCreditmemoItem($item)
    {
        /** @var \Magento\Sales\Model\Order\Item $simpleItem */
        $simpleItem = $this->_getSimpleItem($item->getOrderItem());
        $returnWarehouse = $this->getDoReturnWarehouse($item);

        $this->queryProcess->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' =>  ['warehouse_id' => $returnWarehouse->getId()],
            'condition' => ['entity_id=?' => $item->getId()],
            'table' => $item->getResource()->getMainTable(),
        ], $this->process);

        /*
        $warehouseCreditmemoItemModel = $this->warehouseCreditmemoItemFactory->create();
        $warehouseCreditmemoItemData = [
            'warehouse_id' => $returnWarehouse->getId(),
            'creditmemo_id' => $item->getCreditmemo()->getId(),
            'item_id' => $item->getId(),            
            'order_id' => $item->getOrderItem()->getOrderId(),
            'order_item_id' => $item->getOrderItemId(),
            'product_id' => $simpleItem->getProductId(),
            'qty_refunded' => $this->_getRefundQty($item),
            'subtotal' => $item->getBaseRowTotal(),
            'created_at' => $item->getCreditmemo()->getCreatedAt(),
            'updated_at' => $item->getCreditmemo()->getUpdatedAt(),            
        ];
        $this->queryProcess->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
            'values' =>  [$warehouseCreditmemoItemData], 
            'table' => $warehouseCreditmemoItemModel->getResource()->getMainTable(),
        ], 'creditmemo'); 
        */
    }

    /**
     * update available_qty in warehouse stocks after returned item
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item
     */
    protected function _updateAvailableQtyAfterReturn($item)
    {
        $notShipQty = $this->_getNotShipQtyInRefund($item);
        $refundQty = $this->_getRefundQty($item);
        $globalStockId = WarehouseProductInterface::DEFAULT_SCOPE_ID;
        $orderedWarehouseId = $this->getOrderWarehouse($item->getOrderItemId());
        $refundWarehouseId = $this->catalogInventoryConfiguration->getDefaultScopeId();
        $qtyChanges = array(
            $globalStockId => array(
                WarehouseProductInterface::AVAILABLE_QTY => 0,
            ),
            $orderedWarehouseId => array(
                WarehouseProductInterface::AVAILABLE_QTY => 0,
            ),
            $refundWarehouseId => array(
                WarehouseProductInterface::AVAILABLE_QTY => 0,
            ),
        );

        $qtyChanges[$globalStockId][WarehouseProductInterface::AVAILABLE_QTY] += $notShipQty;
        $qtyChanges[$orderedWarehouseId][WarehouseProductInterface::AVAILABLE_QTY] += $notShipQty;
        $qtyChanges[$refundWarehouseId][WarehouseProductInterface::AVAILABLE_QTY] -= $refundQty;

        $queries = $this->warehouseStockRegistry
                        ->prepareChangeQtys($item->getProductId(), $qtyChanges);
        $this->queryProcess->addQueries($queries, $this->process);

    }

    /**
     * update available_qty after refunded without return
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item
     */
    protected function _updateAvailableQtyAfterRefund($item)
    {
        $notShipQty = $this->_getNotShipQtyInRefund($item);
        $globalStockId = WarehouseProductInterface::DEFAULT_SCOPE_ID;
        $orderedWarehouseId = $this->getOrderWarehouse($item->getOrderItemId());
        $qtyChanges = array(
            $globalStockId => array(
                WarehouseProductInterface::AVAILABLE_QTY => 0,
            ),
            $orderedWarehouseId => array(
                WarehouseProductInterface::AVAILABLE_QTY => 0,
            )
        );

        $qtyChanges[$globalStockId][WarehouseProductInterface::AVAILABLE_QTY] += $notShipQty;
        $qtyChanges[$orderedWarehouseId][WarehouseProductInterface::AVAILABLE_QTY] += $notShipQty;

        $queries = $this->warehouseStockRegistry
                        ->prepareChangeQtys($item->getProductId(), $qtyChanges);
        $this->queryProcess->addQueries($queries, $this->process);
    }

    /**
     * Receive item to returned Warehouse
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item
     */
    protected function _receiveItemToReturnWarehouse($item)
    {
        $simpleItem = $this->_getSimpleItem($item->getOrderItem());
        $returnWarehouse = $this->getDoReturnWarehouse($item);
        $products = [$simpleItem->getProductId() => $this->_getShippedQtyInRefund($item)];
        $creditmemo = $item->getCreditmemo();
        /* receive item to warehouse, also update global stock */
        $this->stockChange->receive($returnWarehouse->getId(), $products, StockActivitySalesRefund::STOCK_MOVEMENT_ACTION_CODE, $creditmemo->getId(), true);
    }

    /**
     * Get Warehouse to return item to
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item
     */
    public function getDoReturnWarehouse($item)
    {
        if(!isset($this->doReturnWarehouses[$item->getId()])) {
            $orderItemId = $item->getOrderItemId();
            /* get return warehouse_id from post data */
            $creditmemoData =  $this->request->getParam('creditmemo');
            $paramOrderItemId = $orderItemId;
            if($parentItem = $item->getOrderItem()->getParentItem()) {
               if($parentItem->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                   $paramOrderItemId = $parentItem->getItemId();
               }
            }

            $returnWarehouseId = null;
            if(isset($creditmemoData['items'][$paramOrderItemId]['warehouse'])) {
                $returnWarehouseId = $creditmemoData['items'][$paramOrderItemId]['warehouse'];
            }

            /* get ordered warehouse Id */
            if(!$returnWarehouseId) {
                $returnWarehouseId = $this->getOrderWarehouse($orderItemId);
            }
            /* get primary warehouse_id */
            if(!$returnWarehouseId) {
                $returnWarehouse = $this->warehouseManagement->getPrimaryWarehouse();
            } else {
                $returnWarehouse = $this->warehouseFactory->create()->load($returnWarehouseId);
            }
            /* allow to change the Warehouse by other extension */
            $this->eventManager->dispatch('inventorysuccess_create_creditmemo_warehouse', [
                                            'warehouse' => $returnWarehouse,
                                            'item' => $item,
                    ]);
            $this->doReturnWarehouses[$item->getId()] = $returnWarehouse;
        }
        return $this->doReturnWarehouses[$item->getId()];
    }

    /**
     * get refund qty of item
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item
     * @return float
     */
    protected function _getRefundQty($item)
    {
        $qty = $item->getQty();
        /**
         * Mark commented
         * check database thay qty trong item con chinh' xac' roi
         */
//        $parentItemId = $item->getOrderItem()->getParentItemId();
//        /* @var $parentItem \Magento\Sales\Model\Order\Creditmemo\Item */
//        $parentItem = $parentItemId ? $item->getCreditmemo()->getItemByOrderId($parentItemId) : false;
//        $qty = $parentItem ? $parentItem->getQty() * $qty : $qty;
        return $qty;
    }

    /**
     * Get not ship qty in refunded item
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item
     * @return float
     */
    protected function _getNotShipQtyInRefund($item)
    {
        $qtyToShip = $this->_getQtyToShipBeforeRefund($item);
        $refundQty = $this->_getRefundQty($item);
        return min($qtyToShip, $refundQty);
    }

    /**
     * Get shipped qty in refunded item
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item
     * @return float
     */
    protected function _getShippedQtyInRefund($item)
    {
        $qtyToShip = $this->_getQtyToShipBeforeRefund($item);
        $refundQty = $this->_getRefundQty($item);
        return max(0, $refundQty - $qtyToShip);
    }

    /**
     * Get qty_to_ship before refunding item
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item
     * @return float
     */
    protected function _getQtyToShipBeforeRefund($item)
    {
        $orderItem = $item->getOrderItem();
        /* get shipped-qty */
        $qtyShipped = $orderItem->getQtyShipped();
        /** @var \Magento\Sales\Model\Order\Item $parentItem */
        if($parentItem = $orderItem->getParentItem()) {
           if($parentItem->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
               $qtyShipped = $parentItem->getQtyShipped();
           } else if ($parentItem->getProductType() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
               if ($parentItem->getProduct()->getShipmentType() == '1'){
                   $qtyShipped = $orderItem->getQtyShipped();
               } else {
                   $bundleShippedQty = $parentItem->getQtyShipped();
                   $bundleOrderedQty = $parentItem->getQtyOrdered();
                   $childItemQtyOrdered = $orderItem->getQtyOrdered();
                   $qtyShipped = $bundleOrderedQty ? $bundleShippedQty * $childItemQtyOrdered / $bundleOrderedQty : 0;
               }
           }
        }

        /* calculate qty-to-ship before refunded */
        $qty = $orderItem->getQtyOrdered()
                - $qtyShipped
                - $orderItem->getQtyCanceled()
                - ($orderItem->getQtyRefunded() - $this->_getRefundQty($item));
        return max($qty, 0);
    }

    /**
     *
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item
     * @return boolean
     */
    public function canProcessItem($item)
    {
        /* check processed item */
        if($this->isProcessedItem($item)) {
            return false;
        }

        /* check manage stock or not */
        if(!$this->isManageStock($item->getOrderItem())) {
            return false;
        }

//        $orderItem = $item->getOrderItem();            
//        if($orderItem->getParentItem()
//                && $orderItem->getParentItem()->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
//            return false;
//        }  

        /* check added item */
        if($item->getWarehouseId()) {
            return false;
        }

        return true;
    }

}
