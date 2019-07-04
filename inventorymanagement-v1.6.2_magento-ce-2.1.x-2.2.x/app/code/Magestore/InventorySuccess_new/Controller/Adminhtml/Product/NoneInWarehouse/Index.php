<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Product\NoneInWarehouse;

/**
 * Class Index
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Product\NoneInWarehouse
 */
class Index extends \Magestore\InventorySuccess\Controller\Adminhtml\AbstractAction
{
    const ADMIN_RESOURCE = 'Magestore_InventorySuccess::product_none_in_warehouse';
    
    protected $_collection;
    
    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context
    ) {
        parent::__construct($context);
        
    }
    
    
    /**
     * Warehouse grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Non-Location Products'));
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
        $resultPage->setActiveMenu('Magestore_InventorySuccess::product_none_in_warehouse');
        $resultPage->addBreadcrumb(__('Inventory'), __('Inventory'));
        $resultPage->addBreadcrumb(__('Non-Location Products'), __('Non-Location Products'));
        return $resultPage;
    }
}