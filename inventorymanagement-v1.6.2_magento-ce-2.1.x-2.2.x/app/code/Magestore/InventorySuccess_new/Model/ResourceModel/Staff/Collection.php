<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\Staff;

/**
 * Class Collection
 * @package Magento\User\Model\ResourceModel\Staff
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
        $this->_init('Magento\User\Model\User', 'Magento\User\Model\ResourceModel\User');
    }

    /**
     * @return $this
     */
    public function addRoleInformation(){
        $this->getSelect()->joinLeft(array('authorization_role' => $this->getTable('authorization_role'))
            , 'main_table.user_id = authorization_role.user_id', array('role_id','role_name'));
        return $this;
    }

    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'user_id') {
            $field = 'main_table.user_id';
        }
        return parent::addFieldToFilter($field, $condition);
    }


}