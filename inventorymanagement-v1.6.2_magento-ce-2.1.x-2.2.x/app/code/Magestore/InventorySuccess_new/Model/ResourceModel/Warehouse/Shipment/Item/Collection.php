<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Shipment\Item;

/**
 * Class Collection
 * @package Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Magestore\InventorySuccess\Model\Warehouse\Shipment\Item',
            'Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Shipment\Item'
        );
    }

    public function getBestSellerProducts($numberProduct, $warehouseId = null){
        if($warehouseId)
            $this->addFieldToFilter('warehouse_id', $warehouseId);
        $this->getSelect()->group('product_id');
        $this->getSelect()->columns([
            'sum_qty_shipped' => new \Zend_Db_Expr('SUM(qty)'),
        ]);
        return $this->addOrder('SUM(qty)')
            ->setPageSize($numberProduct)->setCurPage(1);
    }
}