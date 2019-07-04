<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Order\Item;


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
            'Magestore\InventorySuccess\Model\Warehouse\Order\Item',
            'Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Order\Item'
        );
    }
    
    public function getSalesReport($warehouseId = null, $day = null){
        if($warehouseId)
            $this->getSelect()->where('warehouse_id = ?', $warehouseId);
        if($day){
            $firstDate = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\Stdlib\DateTime\DateTime')
                ->gmtDate('Y-m-d 00:00:00', strtotime('-'.$day.' days'));
            $this->addFieldToFilter('created_at', array('gteq' => $firstDate));
        }
        $this->getSelect()->columns(array('date_without_hour' => 'date(created_at)'));
        return $this;
    }
    
    public function getTotalOrderItem(){
        $this->getSelect()->columns([
            'item_qty_by_day' => 'SUM(`qty_ordered`)',
            'order_by_day' => 'COUNT(warehouse_order_item_id)',
            'revenue_by_day' => 'COUNT(warehouse_order_item_id)',
        ]);
        $this->getSelect()->group(array('date(created_at)'));
        return $this;
    }
}