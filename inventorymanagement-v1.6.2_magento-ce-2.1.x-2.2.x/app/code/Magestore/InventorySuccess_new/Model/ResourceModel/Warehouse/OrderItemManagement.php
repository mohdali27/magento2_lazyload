<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\Warehouse;

use Magestore\InventorySuccess\Model\ResourceModel\AbstractResource;
use Magestore\InventorySuccess\Api\Db\QueryProcessorInterface;

class OrderItemManagement extends AbstractResource
{
    protected function _construct()
    {
        /* do nothing */
    }
    
    
    /**
     * Prepare query to change total_qty, qty_to_ship of orderItem in warehouse
     * Do not commit query
     * 
     * @param int $warehouseId
     * @param int $itemId
     * @param array $changeQtys
     * @return array
     */
    public function prepareChangeItemQty($warehouseId, $itemId, $changeQtys)
    {
        if(!count($changeQtys))
            return [];
        $values = [];
        foreach($changeQtys as $field => $qtyChange) {
            $operation = $qtyChange > 0 ? '+' : '-';
            $values[$field] = new \Zend_Db_Expr($field.$operation.abs($qtyChange));
        }
        $where = ['item_id=?' => $itemId, 'warehouse_id=?' => $warehouseId];

        $query = [
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => $values,
            'condition' => $where,
            'table' => $this->getTable('os_warehouse_order_item')            
        ];
        return $query;
    }    
}