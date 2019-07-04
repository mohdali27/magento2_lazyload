<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\AdjustStock\StockMovementActivity;

class Adjustment extends \Magestore\InventorySuccess\Model\StockMovement\StockMovementActivity
{

    const STOCK_MOVEMENT_ACTION_CODE = 'adjustment';
    const STOCK_MOVEMENT_ACTION_LABEL = 'Stock Adjustment';

    /**
     * Get action reference of stock movement
     *
     * @return string
     */
    public function getStockMovementActionReference($id = null)
    {
        return $this->objectManager->get('Magestore\InventorySuccess\Model\AdjustStock')
                        ->load($id)->getAdjuststockCode();
    }

    /**
     * Get stock movement action URL
     *
     * @param $id
     * @return string|null
     */
    public function getStockMovementActionUrl($id = null)
    {
        return $this->getUrl('inventorysuccess/adjuststock/edit', ['id' => $id]);
    }

}
