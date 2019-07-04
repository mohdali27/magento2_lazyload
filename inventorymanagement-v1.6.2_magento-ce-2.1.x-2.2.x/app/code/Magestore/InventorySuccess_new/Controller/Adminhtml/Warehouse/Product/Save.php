<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Warehouse\Product;

class Save extends \Magestore\InventorySuccess\Controller\Adminhtml\Warehouse\Product\Grid
{
    /**
     * Grid Action
     * Display list of products related to current category
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $warehouseId = $this->getRequest()->getParam('id', false);
        $selectedProduct = json_decode($this->getRequest()->getParam('selected_product'), true);
        if($warehouseId && count($selectedProduct)>0){
            $stockActivityModel = $this->_context->getWarehouseFactory()->create()->getStockActivityProductModel();
            $stockActivityModel->updateStockInGrid($warehouseId, $selectedProduct);
        }
        return parent::execute();
    }
}