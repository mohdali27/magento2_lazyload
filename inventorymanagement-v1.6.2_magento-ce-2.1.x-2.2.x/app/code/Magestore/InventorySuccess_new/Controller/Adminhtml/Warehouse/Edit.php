<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Warehouse;

/**
 * Class Edit
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Warehouse
 */
class Edit extends AbstractWarehouse
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magestore_InventorySuccess::warehouse_view';

    /**
     * Edit Warehouse page
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id', null);
        $warehouse = $this->_initWarehouse($id);

        // 2. Initial checking
        if ($id && (!$warehouse || $warehouse->getWarehouseId() != $id)) {
            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/');
        }

//         if($id && $warehouse->getStatus() == \Magestore\InventorySuccess\Model\Warehouse\Options\Status::STATUS_DISABLED){
//             $this->messageManager->addWarningMessage(__('Disabled warehouse can not be used to order, shipment or refund'));
//         }

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();

        $resultPage->getConfig()->getTitle()->prepend(__('Location'));
        $title = $id ? __('View Location (%1)',$warehouse->getWarehouseCode()) : __('Add a New Location');
        $resultPage->getConfig()->getTitle()
            ->prepend($title);

        return $resultPage;
    }
    
    /**
     * Initialize warehouse model instance
     *
     * @param int|null
     * @return \Magestore\InventorySuccess\Model\Warehouse|false
     */
    protected function _initWarehouse($id = null)
    {
        try {
            $warehouse = $this->_context->getWarehouseFactory()->create()->load($id);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('This location no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        $this->_context->getCoreRegistry()->register('inventorysuccess_warehouse', $warehouse);
        $this->_context->getCoreRegistry()->register('current_warehouse', $warehouse);
        return $warehouse;
    }
    
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        $id = $this->getRequest()->getParam('id', null);
        if($id)
            return $this->_permissionManagement->checkPermission(
                'Magestore_InventorySuccess::warehouse_view',
                $this->_context->getWarehouseFactory()->create()->load($id)
            );
        return parent::_isAllowed();
    }
}
