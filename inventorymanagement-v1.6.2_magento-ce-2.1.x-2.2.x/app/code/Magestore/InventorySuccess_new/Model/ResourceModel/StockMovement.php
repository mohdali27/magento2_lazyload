<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel;

/**
 * Class Warehouse
 * @package Magestore\InventorySuccess\Model\ResourceModel
 */
class StockMovement extends AbstractResource
{
    const TABLE_STOCK_MOVEMENT = 'os_stock_movement';

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_STOCK_MOVEMENT, 'stock_movement_id');
    }
}