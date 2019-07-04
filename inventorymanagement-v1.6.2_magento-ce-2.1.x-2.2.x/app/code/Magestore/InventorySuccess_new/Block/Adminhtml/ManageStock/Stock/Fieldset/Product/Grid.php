<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\ManageStock\Stock\Fieldset\Product;

use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;

/**
 * Class Grid
 * @package Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\Product
 */
class Grid  extends \Magestore\InventorySuccess\Block\Adminhtml\ManageStock\AbstractGridProduct
{
    public function modifyCollection($collection){
        if(!$warehouseId = $this->_isNotAllWarehouse()) {
            $warehouses = $this->warehouseFactory->create()->getCollection();
            $warehouses = $this->_permissionManagement
                ->filterPermission($warehouses, 'Magestore_InventorySuccess::warehouse_stock_view');
            $warehouseIds = $warehouses->getAllIds();
            $collection->getSelect()->where("warehouse_product.". WarehouseProductInterface::WAREHOUSE_ID ." IN ('" . implode("','", $warehouseIds) . "')");
            return $collection;
        }
        $collection->addWarehouseToFilter($warehouseId);
        return $collection;
    }

    /**
     * function to add, remove or modify product grid columns
     *
     * @return $this
     */
    public function modifyColumns(){


        $this->addColumnAfter('price',
            [
                'header' => __('Price'),
                'index' => 'price',
                'sortable' => true,
                'type' => 'currency',
                'currency_code' => (string) $this->_scopeConfig->getValue(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE),
            ], 'name'

        );
        if(!$this->_isNotAllWarehouse()) {
            $this->addColumnAfter('action_view',
                [
                    'header' => __('Product in Locations'),
                    'filters' => false,
                    'sortable' => false,
                    'renderer' => 'Magestore\InventorySuccess\Block\Adminhtml\ManageStock\Stock\Fieldset\Product\Renderer\View'
                ], 'action_view'
            );
        }
        $this->addColumn('status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->_status->getOptionArray()
            ]
        );
        if(!$this->_isNotAllWarehouse()){
            $this->removeColumn('in_warehouse');
            $this->removeColumn('shelf_location');
        }
        return $this;
    }

    /**
     * @return bool|int
     */
    private function _isNotAllWarehouse(){
        $warehouseId = $this->getRequest()->getParam('warehouse_id', null);
        return !$warehouseId || $warehouseId == 0 ? false : $warehouseId;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl("*/manageStock_product/grid", ["_current" => true]);
    }

    /**
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/manageStock_product/save', ["_current" => true]);
    }
}