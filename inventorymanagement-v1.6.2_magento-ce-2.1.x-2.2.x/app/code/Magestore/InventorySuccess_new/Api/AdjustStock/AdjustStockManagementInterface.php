<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\AdjustStock;

use Magestore\InventorySuccess\Api\StockActivity\ProductSelectionManagementInterface;
use Magestore\InventorySuccess\Api\Data\AdjustStock\AdjustStockInterface;

interface AdjustStockManagementInterface extends ProductSelectionManagementInterface
{
    /**
     * Create new stock adjustment
     * 
     * @param \Magestore\InventorySuccess\Api\AdjustStock\Adjuststock $adjustStock
     * @param array $data
     * @param bool $requiredProduct
     * @param bool $requireChange
     * @return AdjustStockInterface
     */
    public function createAdjustment(AdjustStockInterface $adjustStock, $data, $requiredProduct = false, $requireChange = false);
    
    /**
     * Complete an adjustment
     * 
     * @param AdjustStockInterface $adjustStock
     * @param bool $updateCatalog
     */
    public function complete(AdjustStockInterface $adjustStock, $updateCatalog = true);
}

