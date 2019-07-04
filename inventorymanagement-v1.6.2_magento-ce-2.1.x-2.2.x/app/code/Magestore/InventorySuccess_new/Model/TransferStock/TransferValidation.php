<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\TransferStock;

use \Magestore\InventorySuccess\Model\Warehouse\WarehouseStockRegistry;

class TransferValidation
{
    /** @var  \Magestore\InventorySuccess\Model\Warehouse\WarehouseStockRegistryFactory */
    protected $_warehouseStockRegistryFactory;

    /** @var  \Magestore\InventorySuccess\Model\TransferStockFactory */
    protected $_transferStockFactory;

    public function __construct(
        \Magestore\InventorySuccess\Model\Warehouse\WarehouseStockRegistryFactory $warehouseStockRegistryFactory,
        \Magestore\InventorySuccess\Model\TransferStockFactory $transferStockFactory

    )
    {
        $this->_warehouseStockRegistryFactory = $warehouseStockRegistryFactory;
        $this->_transferStockFactory = $transferStockFactory;
    }

    /**
     * validate transfer stock general information form input
     * @param $data
     * @return array
     */
    public function validateTranferGeneralForm($data){
        $is_validate = true;
        $errors = [];
        if(isset($data["source_warehouse_id"]) && isset($data["des_warehouse_id"])){
            if($data["source_warehouse_id"] == $data["des_warehouse_id"]){
                $is_validate = false;
                $errors[] = "Destination Location must be different from Source Location";
            }
        }
        
        if(!isset($data['transferstock_id'])){
            if(!$this->validateTransferstockCode($data['transferstock_code'])){
                $is_validate = false;
                $errors[] = "Transfer Stock Code #".$data['transferstock_code'] ." is aready exits!";
            }
        }
        
        return ["is_validate" =>$is_validate, "errors" => $errors];
    }

    /**
     * check if a the qty of a product is less than available qty in a warehouse or not.
     * @param $products: ['12'=>24, '2'=>22] ([product_id => qty]
     * @param $warehouseId
     * 
     */
    public function validateStock($product_stocks, $warehouseId){
        if(!count($product_stocks)){
            return true;
        }
        /** @var \Magestore\InventorySuccess\Model\Warehouse\WarehouseStockRegistry $stockRegistry */
        $stockRegistry =  $this->_warehouseStockRegistryFactory->create();
        $products = $stockRegistry->getStocks($warehouseId, array_keys($product_stocks));
        foreach ($products as $product){
            $availableQty = $product->getTotalQty() - $product->getQtyToShip();
            if($availableQty < (int)$product_stocks[$product->getProductId()]){
                return false;
            }
        }
        return true;
    }

    /**
     * check if a transferstock_code is valid or not
     * @param $transferstock_code
     * @return bool
     */
    public function validateTransferstockCode($transferstock_code){
        $transferstock = $this->_transferStockFactory->create()->load($transferstock_code,"transferstock_code");

        if($transferstock->getTransferstockId() != null){
            return false;
        }
        return true;
    }
}
