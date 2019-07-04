<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\Stock;

/**
 * Class Grid
 * @package Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\Product
 */
class Grid extends \Magestore\InventorySuccess\Block\Adminhtml\ManageStock\AbstractGridProduct
{
    public function modifyCollection($collection){
        $collection->addWarehouseToFilter($this->getRequest()->getParam('id'));
        return $collection;
    }
    public function modifyColumns(){
        $this->addExportType('*/warehouse/exportWarehouseStockCsv', __('CSV'));
//        $this->addExportType('*/*/exportWarehouseStockXml', __('XML'));
    }
}