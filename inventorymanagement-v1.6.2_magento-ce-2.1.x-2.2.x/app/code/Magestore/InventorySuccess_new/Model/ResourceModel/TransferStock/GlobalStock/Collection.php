<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\TransferStock\GlobalStock;

use Magento\Catalog\Model\Product\Type as SimpleProductType;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;

/**
 * Class Collection
 * @package Magestore\InventorySuccess\Model\ResourceModel\TransferStock\GlobalStock
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    const MAPPING_FIELD = [
        'total_qty' => 'warehouse_product.total_qty',
        'sum_total_qty' => 'SUM(warehouse_product.total_qty)',
        'sum_qty_to_ship' => 'SUM(warehouse_product.total_qty - warehouse_product.qty)',
        'available_qty' => 'SUM(warehouse_product.qty)',
        'total_qty_shipped' => 'SUM(warehouse_shipment_item.qty)',
    ];
    
    /**
     * Init select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['e' => $this->getEntity()->getEntityTable()]);
        $entity = $this->getEntity();
        if ($entity->getTypeId() && $entity->getEntityTable() == \Magento\Eav\Model\Entity::DEFAULT_ENTITY_TABLE) {
            $this->addAttributeToFilter('entity_type_id', $this->getEntity()->getTypeId());
        }
        $this->addAttributeToSelect([
            "name",
            "sku",
            "price",
            "status",
            "image",
            "qty"
        ]);
        /* back again  code alex fix: filter by warehouse
        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $this->joinField(
                'qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1 AND {{table}}.website_id=0',
                'left'
            );
        }
        $this->getSelect()->columns(['available_qty'=>'at_qty.qty']);
        */
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
     * filters products into a collection
     *
     * @param int $warehouseId
     * @return $this
     */
    public function addWarehouseToFilter($warehouseId){
        $this->getSelect()->where('warehouse_product.'. WarehouseProductInterface::WAREHOUSE_ID .' = ?', $warehouseId);
        return $this;
    }

    /**
     * get none warehouse product
     * 
     * @return $this
     */
    public function getNoneWarehouseProduct(){
        $this->getSelect()->having(self::MAPPING_FIELD['sum_total_qty'].' = ?', 0)
            ->having(self::MAPPING_FIELD['sum_qty_to_ship'].' =?', 0);
        return $this;
    }

    /**
     * @param null $warehouse_id
     * @return \Magento\Framework\DataObject
     */
    public function getAllTotalsQty($warehouse_id = null){
        $this->getSelect()->reset(\Magento\Framework\DB\Select::GROUP);
        if($warehouse_id)
            $this->addWarehouseToFilter($warehouse_id);
        $this->getSelect()->group('warehouse_product.'. WarehouseProductInterface::WAREHOUSE_ID);
        return $this->getFirstItem();
    }

    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize()
    {
        if ($this->_totalRecords === null) {
            $sql = $this->getSelect()
                ->reset(\Magento\Framework\DB\Select::LIMIT_COUNT)
                ->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET)
                ->__toString();
            $records = $this->getConnection()->query($sql);
            $result = $records->fetchAll();
            $this->_totalRecords = count($result);
        }
        return intval($this->_totalRecords);
    }

    /**
     * @param string $columnName
     * @param array $filterValue
     * @return $this
     */
    public function addQtyToFilter($columnName, $filterValue){
        if(isset($filterValue['from']))
            $this->getSelect()->having(self::MAPPING_FIELD[$columnName]. ' >= ?', $filterValue['from']);
        if(isset($filterValue['to']))
            $this->getSelect()->having(self::MAPPING_FIELD[$columnName]. ' <= ?', $filterValue['to']);
        return $this;
    }

    /**
     * @param string $columnName
     * @param array $filterValue
     * @return $this
     */
    public function addSheldLocationToFilter($columnName, $filterValue){
        $this->getSelect()->having($columnName. ' LIKE ?', '%'.$filterValue.'%');
        return $this;
    }

    /**
     * get highest qty products
     * 
     * @param int $numberProduct
     * @param null $warehouseId
     * @return $this
     */
    public function getHighestQtyProducts($numberProduct, $warehouseId = null){
        if($warehouseId)
            $this->getSelect()->where('warehouse_product.'. WarehouseProductInterface::WAREHOUSE_ID .' = ?', $warehouseId);
        $this->getSelect()->order(new \Zend_Db_Expr(self::MAPPING_FIELD['sum_total_qty'] . ' DESC'));
        return $this->setPageSize($numberProduct)->setCurPage(1);
    }


    /**
     * @param $numberProduct
     * @param null $warehouseId
     * @return $this
     */
    public function getBestSellerProducts($numberProduct, $warehouseId = null){
        if($warehouseId)
            $this->getSelect()->where('warehouse_product.'. WarehouseProductInterface::WAREHOUSE_ID .' = ?', $warehouseId);
        $this->getSelect()->joinLeft(
            ['warehouse_shipment_item' => $this->getTable('sales_shipment_item')],
            'e.entity_id = warehouse_shipment_item.product_id AND
             warehouse_product.'. WarehouseProductInterface::WAREHOUSE_ID .' = warehouse_shipment_item.warehouse_id',
            '*'
        );
        $this->getSelect()->columns([
            'total_qty_shipped' => new \Zend_Db_Expr(self::MAPPING_FIELD['total_qty_shipped'])
        ]);
        $this->getSelect()->order(new \Zend_Db_Expr(self::MAPPING_FIELD['total_qty_shipped'] . ' DESC'));
        return $this->setPageSize($numberProduct)->setCurPage(1);
    }

    /**
     * @param mixed $field
     * @param null $condition
     * @return $this|\Magento\Framework\Data\Collection\AbstractDb
     */
    public function addFieldToFilter($field, $condition = null)
    {
        foreach (self::MAPPING_FIELD as $key => $value) {
            if($field == $key){
                $field = $value;
            }
        }
        if($field == 'warehouse_product.total_qty') {
            return $this->filterByTotalQty($field, $condition);
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @param string $field
     * @param string $direction
     * @return $this
     */
    public function addOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        foreach (self::MAPPING_FIELD as $key => $value) {
            if($field == $key){
                $field = $value;
            }
        }
        if($field == 'warehouse_product.total_qty') {
            return $this->sortByTotalQty($field, $direction);
        }
        return parent::addOrder($field, $direction);
    }

    /**
     * Add select order
     *
     * @param   string $field
     * @param   string $direction
     * @return  $this
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        foreach (self::MAPPING_FIELD as $key => $value) {
            if($field == $key){
                $field = $value;
            }
        }
        return parent::setOrder($field, $direction);
    }

    /**
     * @param $field
     * @param $condition
     * @return \Magento\Framework\DB\Select
     */
    public function filterByTotalQty($field, $condition)
    {
        if(isset($condition['gteq']) && $condition['gteq']){
            return $this->getSelect()->where($field. ' >= '.$condition['gteq']);
        }
        if(isset($condition['lteq']) && $condition['lteq']){
            return $this->getSelect()->where($field. ' <= '.$condition['lteq']);
        }
    }

    /**
     * @param $field
     * @param $direction
     * @return \Magento\Framework\DB\Select
     */
    public function sortByTotalQty($field, $direction)
    {
        return $this->getSelect()->order($field .' '. $direction);
    }
}