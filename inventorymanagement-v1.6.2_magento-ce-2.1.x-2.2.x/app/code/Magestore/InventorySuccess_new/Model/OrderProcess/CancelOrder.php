<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\OrderProcess;

use Magestore\InventorySuccess\Api\OrderProcess\CancelOrderInterface;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;

class CancelOrder extends OrderProcess implements CancelOrderInterface
{
    /**
     * @var string
     */
    protected $process = 'cancel_order';

    /**
     * execute the process
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     */
    public function execute($item)
    {
        if(!$this->canProcessItem($item)){
            return;
        }

        $this->processCancelItem($item);

        $this->markItemProcessed($item);

        return true;
    }

    /**
     * Process cancel item
     *
     * @param \Magento\Sales\Model\Order\Item $item
     */
    public function processCancelItem($item)
    {
        $this->queryProcess->start($this->process);

        //$this->_updateCanceledQtyInWarehouse($item);
        $this->_updateAvailableQtyInStocks($item);

        $this->queryProcess->process($this->process);
    }

    /**
     * Update qty_canceled of item in Warehouse
     *
     * @param \Magento\Sales\Model\Order\Item $item
     */
    /*
    protected function _updateCanceledQtyInWarehouse($item)
    {
        $orderWarehouseId = $this->getOrderWarehouse($item->getItemId());
        $qtyChanges = ['qty_canceled' =>  $this->_getCanceledQty($item)];
        $query = $this->orderItemManagement
                        ->prepareChangeItemQty($orderWarehouseId, $item->getItemId(), $qtyChanges);
        $this->queryProcess->addQuery($query);
    }
    */
    
    /**
     * update available_qty of product in Locations
     *
     * @param \Magento\Sales\Model\Order\Item $item
     */
    protected function _updateAvailableQtyInStocks($item)
    {
        $globalStockId = WarehouseProductInterface::DEFAULT_SCOPE_ID;
        $orderedWarehouseId = $this->getOrderWarehouse($item->getItemId());
        $processWarehouseId = $this->catalogInventoryConfiguration->getDefaultScopeId();
        $canceledQty = $this->_getCanceledQty($item);
        $qtyChanges = array(
            $globalStockId => array(
                WarehouseProductInterface::AVAILABLE_QTY => 0,
            ),
            $orderedWarehouseId => array(
                WarehouseProductInterface::AVAILABLE_QTY => 0,
            ),
            $processWarehouseId => array(
                WarehouseProductInterface::AVAILABLE_QTY => 0,
            ),
        );
        $qtyChanges[$globalStockId][WarehouseProductInterface::AVAILABLE_QTY] +=  $canceledQty;
        $qtyChanges[$orderedWarehouseId][WarehouseProductInterface::AVAILABLE_QTY] +=  $canceledQty;
        $qtyChanges[$processWarehouseId][WarehouseProductInterface::AVAILABLE_QTY] -=  $canceledQty;

        $queries = $this->warehouseStockRegistry
                        ->prepareChangeQtys($item->getProductId(), $qtyChanges);
        $this->queryProcess->addQueries($queries, $this->process);
    }

    /**
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return boolean
     */
    public function canProcessItem($item)
    {
        /* check processed item */
        if($this->isProcessedItem($item)) {
            return false;
        }
        /* check manage stock or not */
        if(!$this->isManageStock($item)) {
            return false;
        }
        /* check qty-to-cancel of item */
        if(!$this->_getCanceledQty($item)) {
            return false;
        }
        return true;
    }


}
