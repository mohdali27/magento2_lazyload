<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product;

use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product as WarehouseProductResource;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;

/**
 * Class Collection
 * @package Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Barcode
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    const MAPPING_FIELD = [
        'total_qty' => 'main_table.total_qty',
        'qty_to_ship' => 'main_table.total_qty - main_table.qty',
        'available_qty' => 'main_table.qty',
        'warehouse' => 'CONCAT(warehouse_name, " (",warehouse_code,")")'
    ];
    
    
    protected $_idFieldName = WarehouseProductResource::PRIMARY_KEY;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Magestore\InventorySuccess\Model\Warehouse\Product', 
            'Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product'
        );
    }
    
    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addFieldToFilter(WarehouseProductInterface::WAREHOUSE_ID, ['neq' => 0]);
        return $this;
    }    
    
    /**
     * select data from all stocks (include global stock)
     * 
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection
     */
    public function selectAllStocks()
    {
        $this->getSelect()->reset(\Zend_Db_Select::WHERE);
        return $this;
    }

    /**
     * Get count sql
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $this->_renderFilters();

        $countSelect = clone $this->getSelect();
        $countSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);

        if (!count($this->getSelect()->getPart(\Magento\Framework\DB\Select::GROUP))) {
            $countSelect->columns(new \Zend_Db_Expr('COUNT(*)'));
            return $countSelect;
        }
        $countSelect->reset(\Magento\Framework\DB\Select::HAVING);
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);
        $group = $this->getSelect()->getPart(\Magento\Framework\DB\Select::GROUP);
        $countSelect->columns(new \Zend_Db_Expr(("COUNT(DISTINCT ".implode(", ", $group).")")));
        return $countSelect;
    }
    
    /**
     * Retrive Warehouse Stocks by productId
     * 
     * @param int $productId
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection
     */
    public function retrieveWarehouseStocks($productId)
    {
        $this->addFieldToFilter('product_id', $productId);
        $this->getSelect()->joinLeft(['wh' => $this->getTable('os_warehouse')], 
                            'main_table.'. WarehouseProductInterface::WAREHOUSE_ID .' = wh.warehouse_id', 
                            [
                                'warehouse_name', 'warehouse_code'
                            ]);
        $this->getSelect()->columns([
            'qty_to_ship' => new \Zend_Db_Expr(self::MAPPING_FIELD['qty_to_ship']),
            'available_qty' => new \Zend_Db_Expr(self::MAPPING_FIELD['available_qty']),
            'warehouse' => new \Zend_Db_Expr(self::MAPPING_FIELD['warehouse']),
        ]);
        
        return $this; 
    }
    
    /**
     * Retrive Warehouse Stocks by productIds
     * 
     * @param array $productIds
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection
     */
    public function retrieveWarehouseStocksByProductIds($productIds)
    {
        $this->addFieldToFilter('product_id', ['in' => $productIds]);
        $this->getSelect()->joinLeft(['wh' => $this->getTable('os_warehouse')], 
                            'main_table.'. WarehouseProductInterface::WAREHOUSE_ID .' = wh.warehouse_id', 
                            [
                                'warehouse_name', 'warehouse_code'
                            ]);
        $this->getSelect()->columns([
            'available_qty' => new \Zend_Db_Expr(self::MAPPING_FIELD['available_qty']),
            'warehouse' => new \Zend_Db_Expr('CONCAT(warehouse_name, " (",warehouse_code,")")'),
        ]);
        
        return $this; 
    }    
    
    /**
     * @param $warehouseId
     * @return \Magestore\InventorySuccess\Model\Warehouse\Product
     */
    public function getTotalQtysFromWarehouse($warehouseId){
        $this->addFieldToFilter(WarehouseProductInterface::WAREHOUSE_ID, $warehouseId);
        $this->getSelect()->columns([
            'sum_total_qty' => new \Zend_Db_Expr('SUM(total_qty)'),
            'sum_qty_to_ship' => new \Zend_Db_Expr('SUM(total_qty - qty)')
        ])->group(WarehouseProductInterface::WAREHOUSE_ID);
        return $this->getFirstItem();
    }

    /**
     * @param $warehouseId
     * @param $productId
     * @return int
     */
    public function getTotalQtyProductFromWarehouseId($warehouseId, $productId){
        return $this->addFieldToFilter(WarehouseProductInterface::WAREHOUSE_ID, $warehouseId)
            ->addFieldToFilter('product_id', $productId)
            ->getFirstItem()->getTotalQty();
    }


    /**
     * @param string $columnName
     * @param array $filterValue
     * @return $this
     */
    public function addQtyToFilter($columnName, $filterValue){
        if(isset($filterValue['from']))
            $this->getSelect()->where(self::MAPPING_FIELD[$columnName]. ' >= ?', $filterValue['from']);
        if(isset($filterValue['to']))
            $this->getSelect()->where(self::MAPPING_FIELD[$columnName]. ' <= ?', $filterValue['to']);
        return $this;
    }

    /**
     * @param string $columnName
     * @param array $filterValue
     * @return $this
     */
    public function addSheldLocationToFilter($columnName, $filterValue){
        $this->getSelect()->where($columnName. ' LIKE ?', '%'.$filterValue.'%');
        return $this;
    }

    public function addFieldToFilter($field, $condition = null){
        if($field == 'warehouse'){
            $field = new \Zend_Db_Expr(self::MAPPING_FIELD['warehouse']);
        }
        return parent::addFieldToFilter($field, $condition);
    }
}