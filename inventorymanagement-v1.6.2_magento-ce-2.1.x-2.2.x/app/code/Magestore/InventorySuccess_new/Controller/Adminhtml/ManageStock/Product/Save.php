<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\ManageStock\Product;

use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Class Grid
 * @package Magestore\InventorySuccess\Controller\Adminhtml\ManageStock\Product
 */
class Save extends \Magestore\InventorySuccess\Controller\Adminhtml\ManageStock\Product\Grid
{
    public function execute()
    {
        $warehouseId = $this->getRequest()->getParam('warehouse_id', false);
        $selectedProduct = json_decode($this->getRequest()->getParam('selected_product'), true);
        if($warehouseId && count($selectedProduct)>0){
            $stockActivityModel = $this->_context->getWarehouseFactory()->create()->getStockActivityProductModel();
            $stockActivityModel->updateStockInGrid($warehouseId, $selectedProduct);
        }
        return parent::execute();
    }
}