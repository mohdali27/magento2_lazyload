<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Stocktaking;

use \Magestore\InventorySuccess\Api\Stocktaking\StocktakingManagementInterface;
use \Magestore\InventorySuccess\Model\StockActivity\ProductSelectionManagement;
use Magestore\InventorySuccess\Api\Data\Stocktaking\StocktakingInterface;

/**
 * Class StocktakingManagement
 * @package Magestore\InventorySuccess\Model\Stocktaking
 */
class StocktakingManagement extends ProductSelectionManagement implements StocktakingManagementInterface
{
    /**
     * Create new Stock Stocktaking
     * 
     * @param StocktakingInterface $stocktaking
     * @param array $data
     * @return StocktakingInterface
     */ 
    public function createStocktaking(StocktakingInterface $stocktaking, $data) {
        $createdAt =  isset($data[StocktakingInterface::CREATED_AT]) ?
                     $data[StocktakingInterface::CREATED_AT] :
                     $this->_systemHelper->getCurTime();
        $createdBy =  isset($data[StocktakingInterface::CREATED_BY]) ?
                    $data[StocktakingInterface::CREATED_BY] :
                    $this->_systemHelper->getCurUser()->getUserName();
        $stocktakingCode =  isset($data[StocktakingInterface::STOCKTAKING_CODE]) ?
                   $data[StocktakingInterface::STOCKTAKING_CODE] :
                   $this->generateCode();
        $status =  isset($data[StocktakingInterface::STATUS]) ?
                   $data[StocktakingInterface::STATUS] :
                   '0';
        /* prepare data for stock stocktaking */

        $stocktakeAt = date("d-m-Y H:i:s", strtotime($data[StocktakingInterface::STOCKTAKE_AT]));

        $stocktaking->setReason($data[StocktakingInterface::REASON])
                    ->setWarehouseId($data[StocktakingInterface::WAREHOUSE_ID])
                    ->setWarehouseName($data[StocktakingInterface::WAREHOUSE_NAME])
                    ->setWarehouseCode($data[StocktakingInterface::WAREHOUSE_CODE])
                    ->setParticipants($data[StocktakingInterface::PARTICIPANTS])
                    ->setStocktakeAt($stocktakeAt)
                    ->setStatus($status)
                    ->setCreatedAt($createdAt)
                    ->setCreatedBy($createdBy)
                    ->setStocktakingCode($stocktakingCode)
                ;
        if(isset($data[StocktakingInterface::VERIFIED_BY]))
            $stocktaking->setVerifiedBy($data[StocktakingInterface::VERIFIED_BY]);
        if(isset($data[StocktakingInterface::VERIFIED_AT]))
            $stocktaking->setVerifiedAt($data[StocktakingInterface::VERIFIED_AT]);
        if(isset($data[StocktakingInterface::CONFIRMED_BY]))
            $stocktaking->setConfirmedBy($data[StocktakingInterface::CONFIRMED_BY]);
        if(isset($data[StocktakingInterface::CONFIRMED_AT]))
            $stocktaking->setConfirmedAt($data[StocktakingInterface::CONFIRMED_AT]);

        /* load warehouse data if $data[StocktakingInterface::WAREHOUSE_NAME] is null */
        if(!$data[StocktakingInterface::WAREHOUSE_NAME]) {
            $warehouse = $this->_warehouseFactory->create()->load($data[StocktakingInterface::WAREHOUSE_ID]);
            $stocktaking->setWarehouseName($warehouse->getWarehouseName());
            $stocktaking->setWarehousecode($warehouse->getWarehouseCode());
        }

        if(isset($data['products']) && count($data['products'])) {
            /* load old_qty of products in warehouse */
            $whProducts = $this->_warehouseStockRegsitry->getStocks($data[StocktakingInterface::WAREHOUSE_ID], array_keys($data['products']));
            if($whProducts->getSize()) {
                foreach ($whProducts as $whProduct) {
                    //if (isset($data['products'][$whProduct->getProductId()]['stocktaking_qty']) &&
                    //    $data['products'][$whProduct->getProductId()]['stocktaking_qty'] == $whProduct->getTotalQty()) {
                    //} else {
                        $data['products'][$whProduct->getProductId()]['old_qty'] = $whProduct->getTotalQty();
                    //}
                }
            }
        }
        /* create Product Selection */
        $this->createSelection($stocktaking, $data);

        return $stocktaking;
    }
    
    /**
     * Generate unique code of Stock Stocktaking
     * 
     * @return string
     */
    public function generateCode() {
        return parent::generateUniqueCode(StocktakingInterface::PREFIX_CODE);
    }

    /**
     * get different product list
     *
     * @return string
     */
    public function getDifferentProducts($stocktaking) {
        $products = $stocktaking->getStockActivityProductModel()->getCollection()
                         ->getStocktakingDifferentProducts($stocktaking->getId());
        return $products;
    }

}