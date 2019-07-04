<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\WarehouseStoreViewMap;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\DB\Select;
use Magento\Framework\ObjectManagerInterface;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product as WarehouseProductResource;

/**
 * Class Collection
 * @package Magestore\InventorySuccess\Model\ResourceModel\WarehouseStoreViewMap
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';
    
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Magestore\InventorySuccess\Model\WarehouseStoreViewMap',
            'Magestore\InventorySuccess\Model\ResourceModel\WarehouseStoreViewMap'
        );
    }
}