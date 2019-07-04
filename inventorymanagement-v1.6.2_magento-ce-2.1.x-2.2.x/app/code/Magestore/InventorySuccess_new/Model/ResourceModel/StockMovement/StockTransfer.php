<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\StockMovement;

use Magestore\InventorySuccess\Model\ResourceModel\AbstractResource;


class StockTransfer extends AbstractResource
{
    const TABLE_STOCK_MOVEMENT = 'os_stock_transfer';

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_STOCK_MOVEMENT, 'stock_transfer_id');
    }
}