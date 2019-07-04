<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Warehouse\Locations;

use Magestore\InventorySuccess\Controller\Adminhtml\Warehouse\AbstractWarehouse;

/**
 * Class Mapping
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Warehouse\Locations
 */
class Mapping extends AbstractWarehouse
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()->prepend(__('Mapping Locations - Locations'));
        return $resultPage;
    }
}
