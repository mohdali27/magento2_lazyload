<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Warehouse;

/**
 * Class Delete
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Warehouse
 */
class Delete extends AbstractWarehouse
{
    const ADMIN_RESOURCE = 'Magestore_InventorySuccess::inventorysuccess_warehouse';
    
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        $warehouse = $this->_context->getWarehouseFactory()->create();
        $warehouse->getResource()->load($warehouse, $id);
        $product = $warehouse->getStockActivityProductModel()->getCollection()
            ->getTotalQtysFromWarehouse($warehouse->getWarehouseId());
        if($product->getSumTotalQty()>0 || $product->getSumQtyToShip()>0){
            $this->messageManager->addErrorMessage(__('Can not delete this location because it still contains some products'));
            return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        }
        try {
            $warehouse->getResource()->delete($warehouse);
            $this->messageManager->addSuccessMessage(__('Location was successfully deleted'));
        }catch (\Exception $e){
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}