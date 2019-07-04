<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Api\StockMovement;


interface StockMovementActivityInterface
{
    /**
     * Get action code of stock movement
     *
     * @return string
     */
    public function getStockMovementActionCode();

    /**
     * Get action label of stock movement
     *
     * @return string
     */
    public function getStockMovementActionLabel();

    /**
     * Get action reference of stock movement
     *
     * @param $id
     * @return string
     */
    public function getStockMovementActionReference($id = null);
    
    /**
     * Get stock movement action URL
     *
     * @param $id
     * @return string|null
     */
    public function getStockMovementActionUrl($id);
    
    
    
}