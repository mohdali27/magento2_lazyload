<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Warehouse;

/**
 * Class Index
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Warehouse
 */
class Index extends AbstractWarehouse
{
    const ADMIN_RESOURCE = 'Magestore_InventorySuccess::warehouse_list';
    
    /**
     * Warehouse grid
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Location'));
        return $resultPage;
    }
}