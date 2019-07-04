<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Shipment;

/**
 * Class Product
 * @package Magestore\InventorySuccess\Model\ResourceModel\Warehouse
 */
class Item extends \Magestore\InventorySuccess\Model\ResourceModel\AbstractResource
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        //$this->_init('os_warehouse_shipment_item', 'entity_id');
        $this->_init('sales_shipment_item', 'warehouse_shipment_item_id');
    }
}