<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Plugin\CatalogInventory\Model\ResourceModel\Indexer\Stock;


class Bundle extends \Magento\Bundle\Model\ResourceModel\Indexer\Stock
{
    /**
     * @var array
     */
    protected $warehouseIds;

    /**
     * Get the select object for get stock status by product ids
     *
     * @param int|array $entityIds
     * @param bool $usePrimaryTable use primary or temporary index table
     * @return \Magento\Framework\DB\Select
     */
    protected function _getStockStatusSelect($entityIds = null, $usePrimaryTable = false)
    {
        $select = parent::_getStockStatusSelect($entityIds, $usePrimaryTable);

        $warehouseId = $this->_getAllWarehouseIds();

        $websiteId = array_merge($warehouseId, [0]);

        $select->orWhere("cis.website_id IN (?)", $websiteId)
            ->where('e.type_id = ?', $this->getTypeId());
        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }
        
        return $select;
    }

    /**
     * Get all warehouse ids
     *
     * @return array
     */
    protected function _getAllWarehouseIds()
    {
        if (!$this->warehouseIds) {
            $connection = $this->getConnection();
            $select = $connection->select()
                ->from($this->getTable('os_warehouse'), ['warehouse_id']);
            $query = $connection->query($select);
            $this->warehouseIds = [];
            while ($row = $query->fetch()) {
                $this->warehouseIds[] = $row['warehouse_id'];
            }
        }
        return $this->warehouseIds;
    }


    /**
     * Update stock status index table (INSERT ... ON DUPLICATE KEY UPDATE ...)
     *
     * @param array $data
     * @return $this
     */
    protected function _updateIndexTable($data)
    {
        if (empty($data)) {
            return $this;
        }

        $warehouseIds = $this->_getAllWarehouseIds();

        foreach ($data as &$item) {
            if (in_array($item['website_id'], $warehouseIds))
                $item['stock_id'] = $item['website_id'];
        }

        $connection = $this->getConnection();
        $connection->insertOnDuplicate($this->getMainTable(), $data, ['qty', 'stock_status']);

        return $this;
    }
}