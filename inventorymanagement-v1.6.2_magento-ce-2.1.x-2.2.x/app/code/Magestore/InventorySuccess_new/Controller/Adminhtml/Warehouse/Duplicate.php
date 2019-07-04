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
class Duplicate extends AbstractWarehouse
{
    const ADMIN_RESOURCE = 'Magestore_InventorySuccess::warehouse_duplicate';

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        $duplicateServiceInterface = $this->_objectManager
            ->get('Magestore\InventorySuccess\Api\Warehouse\WarehouseDuplicateServiceInterface');
        try {
            $newWarehouse = $duplicateServiceInterface->duplicateWarehouse($id);
            if($newWarehouse && $newWarehouse->getId()) {
                $this->messageManager->addSuccessMessage(__('Location was successfully duplicated'));
                return $resultRedirect->setPath('*/*/edit', ['id' => $newWarehouse->getId()]);
            } else {
                $this->messageManager->addSuccessMessage(__('Cannot duplicate location'));
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }catch (\Exception $e){
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
        }
    }
}