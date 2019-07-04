<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Api\StockMovement;

use Magestore\InventorySuccess\Api\Data\StockMovement\StockMovementInterface;

interface StockTransferServiceInterface
{
    /**
     * Add stock movement record to stock transfer
     *
     * @param \Magestore\InventorySuccess\Api\Data\StockMovement\StockMovementInterface $stockMovement
     * @return string
     */
    public function addStockMovement(StockMovementInterface $stockMovement);
    
    
    /**
     * Add all stock movement records to transfer
     */
    public function addAllStockMovement();

}