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
class Transfer extends \Magestore\InventorySuccess\Controller\Adminhtml\AbstractAction
{

    /**
     * Warehouse grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Receipt/ Delivery History'));
        return $resultPage;
    }
    
    /**
     * Init layout, menu and breadcrumb
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Magestore_InventorySuccess::stock_transfer');
        $resultPage->addBreadcrumb(__('Inventory'), __('Inventory'));
        $resultPage->addBreadcrumb(__('Receipt/ Delivery History'), __('Receipt/ Delivery History'));
        return $resultPage;
    }
}