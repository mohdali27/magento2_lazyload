<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\StockMovement;

/**
 * Class Collection
 * @package Magestore\InventorySuccess\Model\ResourceModel\Warehouse
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'stock_movement_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\InventorySuccess\Model\StockMovement', 'Magestore\InventorySuccess\Model\ResourceModel\StockMovement');
    }


    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'product') {
            $field = new \Zend_Db_Expr('product_sku');
        }
        return parent::addFieldToFilter($field, $condition);
    }
}