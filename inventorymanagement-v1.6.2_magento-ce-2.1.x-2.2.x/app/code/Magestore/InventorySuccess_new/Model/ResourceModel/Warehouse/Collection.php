<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\Warehouse;

/**
 * Class Collection
 * @package Magestore\InventorySuccess\Model\ResourceModel\Warehouse
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'warehouse_id';

    const MAPPING_FIELD = [
        'warehouse_id' => 'main_table.warehouse_id',
        'warehouse' => 'CONCAT(warehouse_name, " (",warehouse_code,")")'
    ];

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\InventorySuccess\Model\Warehouse', 'Magestore\InventorySuccess\Model\ResourceModel\Warehouse');
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()])
            ->columns(
                array('warehouse' => new \Zend_Db_Expr(self::MAPPING_FIELD['warehouse']))
            );
        return $this;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getFirstWarehouse()
    {
        return $this->setPageSize(1)->setCurPage(1)->getFirstItem();
    }
    
    /**
     * 
     * @return array
     */
    public function toOptionArray()
    {
        return parent::_toOptionArray('warehouse_id','warehouse');
    }

    
    public function addFieldToFilter($field, $condition = null)
    {
        if(in_array($field, array_keys(self::MAPPING_FIELD)))
            $field = new \Zend_Db_Expr(self::MAPPING_FIELD[$field]);
        return parent::addFieldToFilter($field, $condition);
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
        if(in_array($field, array_keys(self::MAPPING_FIELD)))
            $field = new \Zend_Db_Expr(self::MAPPING_FIELD[$field]);
        return parent::setOrder($field, $direction);
    }
}