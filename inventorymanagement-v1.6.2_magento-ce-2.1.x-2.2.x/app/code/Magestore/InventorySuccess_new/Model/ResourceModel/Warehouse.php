<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel;

/**
 * Class Warehouse
 * @package Magestore\InventorySuccess\Model\ResourceModel
 */
class Warehouse extends AbstractResource
{
    const TABLE_INVENTORY_WARHOURSE = 'os_warehouse';

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_INVENTORY_WARHOURSE, 'warehouse_id');
    }

    /**
     * Process post data before saving
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$this->isValidPostData($object)) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Required field is null')
            );
        }

        return parent::_beforeSave($object);
    }

    /**
     *  Check whether post data is valid
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    protected function isValidPostData(\Magento\Framework\Model\AbstractModel $object)
    {
        if (is_null($object->getData('warehouse_name')) || is_null($object->getData('warehouse_code'))) {
            return false;
        }
        return true;
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterSave($object);

        /* update stock entity */
        $this->_updateStock($object);

        return $this;
    }

    /**
     * Perform actions after object delete
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterDelete($object);

        /* delete linked stock */
        $this->_deleteStock($object);

        return $this;
    }

    /**
     * Update cataloginventory/stock, do not update default stock
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magestore\InventorySuccess\Model\ResourceModel
     */
    protected function _updateStock(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId() != \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID) {
            $stockData = array(
                'stock_id' => $object->getWarehouseId(),
                //'website_id' => \Magento\CatalogInventory\Model\Configuration::DEFAULT_WEBSITE_ID,
                'website_id' => $object->getWarehouseId(),
                'stock_name' => $object->getWarehouseCode(),
            );
            $this->getConnection()->insertOnDuplicate($this->getTable('cataloginventory_stock'), $stockData);
        }
        return $this;
    }

    /**
     * Delete cataloginventory/stock, do not delete default stock
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magestore\InventorySuccess\Model\ResourceModel
     */
    protected function _deleteStock(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getId() != \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID) {
            $this->getConnection()->delete(
                $this->getTable('cataloginventory_stock'),
                $this->getConnection()->quoteInto('stock_id=?', $object->getId())
            );
            $this->getConnection()->delete(
                $this->getTable(Warehouse\Product::MAIN_TABLE),
                $this->getConnection()->quoteInto('website_id=?', $object->getId())
            );
        }
    }
}