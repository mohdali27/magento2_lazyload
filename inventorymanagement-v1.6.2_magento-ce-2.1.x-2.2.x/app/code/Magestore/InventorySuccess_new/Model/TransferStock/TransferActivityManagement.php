<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\TransferStock;

use \Magestore\InventorySuccess\Model\StockActivity\ProductSelectionManagement;
use \Magestore\InventorySuccess\Api\Data\TransferStock\TransferActivityInterface;
use \Magestore\InventorySuccess\Model\TransferStock as TransferStockModel;
use \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct as TransferStockProductResource;
use \Magestore\InventorySuccess\Model\TransferStock\StockMovementActivity\Transfer as TransferStockMovementActivity;


class TransferActivityManagement extends ProductSelectionManagement implements
    \Magestore\InventorySuccess\Api\TransferStock\TransferActivityManagementInterface
{

    /** @var  \Magestore\InventorySuccess\Model\TransferStockFactory */
    protected $_transferStockFactory;

    /** @var  \Magestore\InventorySuccess\Model\TransferStock\TransferActivityFactory */
    protected $_transferActivityFactory;

    /** @var  \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProductFactory */
    protected $_transferStockProductResourceFactory;


    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\ProductSelectionManagementFactory $resourceProductSelectionManagementFactory,
        \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface $stockChange,
        \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegsitry,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        \Magestore\InventorySuccess\Api\IncrementIdManagementInterface $incrementIdManagement,
        \Magestore\InventorySuccess\Api\Helper\SystemInterface $systemHelper,
        \Magestore\InventorySuccess\Model\TransferStockFactory $transferStockFactory,
        \Magestore\InventorySuccess\Model\TransferStock\TransferActivityFactory $transferActivityFactory,
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProductFactory $transferStockProductResourceFactory
    )
    {
        $this->_resourceProductSelectionManagementFactory = $resourceProductSelectionManagementFactory;
        $this->_stockChange = $stockChange;
        $this->_warehouseStockRegsitry = $warehouseStockRegsitry;
        $this->_warehouseFactory = $warehouseFactory;
        $this->_incrementIdManagement = $incrementIdManagement;
        $this->_systemHelper = $systemHelper;
        $this->_transferStockFactory = $transferStockFactory;
        $this->_transferActivityFactory = $transferActivityFactory;
        $this->_transferStockProductResourceFactory = $transferStockProductResourceFactory;
    }

    public function updateStock(TransferActivityInterface $transferActivity) {
        $products = $this->getProducts($transferActivity);
        $productData = [];
        if($products->getSize()) {
            foreach($products as $product) {
                $productData[$product->getProductId()] = $product->getQty();
            }
        }

        $transferstockId = $transferActivity->getTransferstockId();
        $transferStock = $this->_transferStockFactory->create()->load($transferstockId);

        if($transferActivity->getActivityType() == TransferActivityInterface::ACTIVITY_TYPE_RETURN){
            /* return before receipt -> receive stock for sourceWarehouse (return from delivery) */
            $warehouseId = $transferStock->getSourceWarehouseId();
            $this->_stockChange->receive($warehouseId, $productData, TransferStockMovementActivity::STOCK_MOVEMENT_ACTION_CODE, $transferStock->getTransferstockId());
            return;
        }
        if ($transferActivity->getActivityType() == TransferActivityInterface::ACTIVITY_TYPE_DELIVERY){
            $warehouseId = $transferStock->getSourceWarehouseId();
            $this->_stockChange->issue($warehouseId, $productData, TransferStockMovementActivity::STOCK_MOVEMENT_ACTION_CODE, $transferStock->getTransferstockId());
        }
        else{
            $warehouseId = $transferStock->getDesWarehouseId();
            $this->_stockChange->receive($warehouseId, $productData, TransferStockMovementActivity::STOCK_MOVEMENT_ACTION_CODE, $transferStock->getTransferstockId());
        }
        /* update stocks in warehouse & global */
        //$this->_stockChange->massChange($warehouseId, $productData);
    }

    public function canEditProductList($transferstockId){
        $transferStockActivity = $this->_transferActivityFactory->create();
        $collection = $transferStockActivity->getCollection();
        $collection->addFieldToFilter("transferstock_id", $transferstockId);
        if($collection->getSize()){
            return false;
        }
        return true;
    }

    /**
     * update qty_delivered and qty_received of transfer_stock_product table when
     * create delivery or receiving
     * @param $activity_products
     * @param $activity_type
     */
    public function updateTransferstockProductQtySummary($transferstockId, $activity_products, $activity_type){
        $transferStock = $this->_transferStockFactory->create()->load($transferstockId);

        $transferStockProductResoure = $this->_transferStockProductResourceFactory->create();
        $qtys = [];
        $qtyChanged = 0;

        foreach ($activity_products as $product){
            $qtys[$product['id']] = array($product['id'] =>$product['qty']);
            $qtyChanged += $product['qty'];
        }


        if($activity_type == TransferActivityInterface::ACTIVITY_TYPE_RECEIVING){
            $field = TransferStockProductResource::FIELD_QTY_RECEIVED;
            $qtyChanged = $transferStock->getData("qty_received") + $qtyChanged;
            $transferStock->setData("qty_received", $qtyChanged);
        }elseif($activity_type == TransferActivityInterface::ACTIVITY_TYPE_RETURN){
            $field = TransferStockProductResource::FIELD_QTY_RETURNED;
            $qtyChanged = $transferStock->getData("qty_returned") + $qtyChanged;
            $transferStock->setData("qty_returned", $qtyChanged);
        }
        else{
            $field = TransferStockProductResource::FIELD_QTY_DELIVERED;
            $qtyChanged = $transferStock->getData("qty_delivered") + $qtyChanged;
            $transferStock->setData("qty_delivered", $qtyChanged);
        }

        //try {
            $transferStock->save();
        //} catch (\Exception $e) {

        //}

        $transferStockProductResoure->updateQty($transferstockId, $qtys,$field);
    }
}
