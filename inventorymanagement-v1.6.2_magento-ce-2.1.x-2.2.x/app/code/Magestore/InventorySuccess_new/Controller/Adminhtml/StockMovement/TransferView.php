<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\StockMovement;


/**
 * Class Index
 * @package Magestore\InventorySuccess\Controller\Adminhtml\StockMovement
 */
class TransferView extends \Magestore\InventorySuccess\Controller\Adminhtml\AbstractAction
{

    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Magestore\InventorySuccess\Model\StockMovement\StockTransfer');

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This note no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/transfer');
            }
        } else {
            return $resultRedirect->setPath('*/*/transfer');
        }

        $this->_coreRegistry->register('stock_transfer', $model);

        $resultPage = $this->_resultPageFactory->create();
        $this->_initAction($resultPage)->addBreadcrumb(
            __('Receipt/ Delivery Detail'),
            __('Receipt/ Delivery Detail')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Receipt/ Delivery Detail'));
        return $resultPage;
    }
    
    /**
     * Init layout, menu and breadcrumb
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction($resultPage)
    {
        $resultPage->setActiveMenu('Magestore_InventorySuccess::stock_transfer');
        $resultPage->addBreadcrumb(__('Inventory'), __('Inventory'));
        $resultPage->addBreadcrumb(__('Receipt/ Delivery Detail'), __('Receipt/ Delivery Detail'));
        return $resultPage;
    }
}