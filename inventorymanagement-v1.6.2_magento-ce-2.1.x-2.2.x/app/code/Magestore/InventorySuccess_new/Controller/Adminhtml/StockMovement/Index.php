<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\StockMovement;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Index
 * @package Magestore\InventorySuccess\Controller\Adminhtml\StockMovement
 */
class Index extends \Magestore\InventorySuccess\Controller\Adminhtml\AbstractAction
{

    /**
     * Warehouse grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Stock Movement'));
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
        $resultPage->setActiveMenu('Magestore_InventorySuccess::stock_movement');
        $resultPage->addBreadcrumb(__('Inventory'), __('Inventory'));
        $resultPage->addBreadcrumb(__('Stock Movement'), __('Stock Movement'));
        return $resultPage;
    }
}