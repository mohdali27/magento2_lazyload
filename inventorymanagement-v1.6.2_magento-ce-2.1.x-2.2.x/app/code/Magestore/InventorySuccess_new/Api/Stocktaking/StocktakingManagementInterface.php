<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Stocktaking;

use Magestore\InventorySuccess\Api\StockActivity\ProductSelectionManagementInterface;
use Magestore\InventorySuccess\Api\Data\Stocktaking\StocktakingInterface;

interface StocktakingManagementInterface extends ProductSelectionManagementInterface
{
    /**
     * Create new stock Stocktaking
     * 
     * @param \Magestore\InventorySuccess\Api\Stocktaking\Stocktaking $stocktaking
     * @param array $data
     */
    public function createStocktaking(StocktakingInterface $stocktaking, $data);
    
    /**
     * Complete a stocktaking
     * 
     * @param StocktakingInterface $stocktaking
     */
//    public function complete(StocktakingInterface $stocktaking);
}

