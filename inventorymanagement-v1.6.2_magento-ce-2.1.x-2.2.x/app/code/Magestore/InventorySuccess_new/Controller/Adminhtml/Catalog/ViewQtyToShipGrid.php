<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Catalog;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Index
 * @package Magestore\InventorySuccess\Controller\Adminhtml\StockMovement
 */
class ViewQtyToShipGrid extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->_view->loadLayout();
        $grid = $this->_view->getLayout()->createBlock('Magestore\InventorySuccess\Block\Adminhtml\ManageStock\ViewQtyToShip\Grid')->toHtml();
        $this->getResponse()->setBody($grid);
    }
}