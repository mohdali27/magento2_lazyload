<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\Sales\Order\Item;


class Collection extends \Magento\Sales\Model\ResourceModel\Order\Item\Collection
{
    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()]);
        $this->getSelect()->joinLeft(
            ['warehouse_order' => $this->getTable('os_warehouse_order_item')],
            'main_table.item_id = warehouse_order.item_id',
            '*'
        );
        return $this;
    }

    public function getSalesReport($warehouseId = null, $day = null){
        if($warehouseId)
            $this->getSelect()->where('main_table.warehouse_id = ?', $warehouseId);
        if($day){
            $firstDate = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\Stdlib\DateTime\DateTime')
                ->gmtDate('Y-m-d 00:00:00', strtotime('-'.$day.' days'));
            $this->addFieldToFilter('main_table.created_at', array('gteq' => $firstDate));
        }
        $this->getSelect()->columns(array('date_without_hour' => 'date(main_table.created_at)'));
        return $this;
    }

    public function getTotalOrderItem(){
        $this->getSelect()->columns([
            'item_qty_by_day' => 'SUM(main_table.qty_ordered)',
            'order_by_day' => 'COUNT(main_table.item_id)',
            'revenue_by_day' => 'SUM(main_table.base_row_total_incl_tax)',
        ]);
        $this->getSelect()->group(array('date(main_table.created_at)'));
        return $this;
    }
}
