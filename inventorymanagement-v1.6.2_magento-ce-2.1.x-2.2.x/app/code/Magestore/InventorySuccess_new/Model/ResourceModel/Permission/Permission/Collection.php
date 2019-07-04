<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\Permission\Permission;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\DB\Select;

/**
 * Class Collection
 * @package Magestore\InventorySuccess\Model\ResourceModel\Permission\Permission
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
        $this->_init('Magestore\InventorySuccess\Model\Permission\Permission', 'Magestore\InventorySuccess\Model\ResourceModel\Permission\Permission');
    }

    /**
     * @return $this
     */
    public function joinWarehouse()
    {
        $this->getSelect()->joinLeft(array('os_warehouse' => $this->getTable('os_warehouse'))
            , 'main_table.object_id = os_warehouse.warehouse_id', array('warehouse_name'))
            ->columns(['object_name' => 'os_warehouse.warehouse_name'])
            ->columns(['id' => 'main_table.object_id']);
        return $this;
    }

    /**
     * @return $this
     */
    public function joinStaff()
    {
        $this->getSelect()->joinLeft(array('admin_user' => $this->getTable('admin_user'))
            , 'main_table.user_id = admin_user.user_id', array('username'))
            ->columns(['username' => 'admin_user.username'])
            ->columns(['id' => 'main_table.user_id']);
        return $this;
    }

    /**
     * @return array
     */
    public function getAllObjectIDs()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Select::ORDER);
        $idsSelect->reset(Select::LIMIT_COUNT);
        $idsSelect->reset(Select::LIMIT_OFFSET);
        $idsSelect->reset(Select::COLUMNS);
        $idsSelect->columns('main_table.object_id');
        return $this->getConnection()->fetchCol($idsSelect);
    }
}