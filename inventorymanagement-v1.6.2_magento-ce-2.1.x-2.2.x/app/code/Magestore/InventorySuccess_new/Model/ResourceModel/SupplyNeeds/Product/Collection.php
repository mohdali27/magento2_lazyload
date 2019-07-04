<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\SupplyNeeds\Product;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     *
     * @var bool
     */
    protected $_isGroupSql = false;

    /*
     * @var bool
     */
    protected $_resetHaving = false;

    /**
     * @param $value
     * @return $this
     */
    public function setIsGroupCountSql($value) {
        $this->_isGroupSql = $value;
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setResetHaving($value) {
        $this->_resetHaving = $value;
        return $this;
    }

    /**
     * Get collection size
     *
     * @return int
     */
    public function getSize() {
        if (is_null($this->_totalRecords)) {
            $sql = $this->getSelectCountSql();
            if ($this->_isGroupSql) {
                $this->_totalRecords = count($this->getConnection()->fetchAll($sql, $this->_bindParams));
            } else {
                $this->_totalRecords = ($this->getConnection()->fetchOne($sql, $this->_bindParams));
            }
        }
        return intval($this->_totalRecords);
    }

    /**
     * Get count sql
     *
     * @return Zend_DB_Select
     */
    public function getSelectCountSql() {
        if ($this->_isGroupSql) {
            $this->_renderFilters();
            $countSelect = clone $this->getSelect();
            $countSelect->reset(\Zend_Db_Select::ORDER);
            $countSelect->reset(\Zend_Db_Select::LIMIT_COUNT);
            $countSelect->reset(\Zend_Db_Select::LIMIT_OFFSET);
            $countSelect->reset(\Zend_Db_Select::COLUMNS);
            if (count($this->getSelect()->getPart(\Zend_Db_Select::GROUP)) > 0) {
                //$countSelect->reset(Zend_Db_Select::GROUP);
                if ($this->_resetHaving) {
//                    $countSelect->reset(\Zend_Db_Select::HAVING);
                }
                //$countSelect->distinct(true);
                $group = $this->getSelect()->getPart(\Zend_Db_Select::GROUP);
                $countSelect->columns("COUNT(DISTINCT " . implode(", ", $group) . ")");
            } else {
                $countSelect->columns('COUNT(*)');
            }
            return $countSelect;
        }
        return parent::getSelectCountSql();
    }

    /**
     * @param $arr
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setItems($arr) {
        //remove all item
        foreach ($this->getItems() as $key => $item) {
            $this->removeItemByKey($key);
        }
        foreach ($arr as $item) {
            $this->addItem($item);
        }
        return $this;
    }

    /**
     * Set size to collection
     *
     * @param int $size
     */
    public function setSize($size) {
        $this->_totalRecords = intval($size);
    }

    /* Add by Kai - fix export supplyneed on Modal */
    public function getTotalCount() {
        return $this->getSize();
    }
    /* End by Kai - fix export supplyneed on Modal */

}