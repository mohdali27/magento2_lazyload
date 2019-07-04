<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\Notification;

use Magento\Framework\Controller\ResultFactory;

/**
 *
 * @category Magestore
 * @package  Magestore_Inventoryplus
 * @module   Inventoryplus
 * @author   Magestore Developer
 */
class Index extends \Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\AbstractLowStockNotification
{

    const ADMIN_RESOURCE = 'Magestore_InventorySuccess::list_notification';

    protected $_productIds;
    /**
     * Index action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Magestore_InventorySuccess::list_notification');
        $resultPage->getConfig()->getTitle()->prepend(__('Notification log'));
        $resultPage->addBreadcrumb(__('Inventory Success'), __('Inventory Success'));
        $resultPage->addBreadcrumb(__('Notification log'), __('Notification log'));

        return $resultPage;
    }
}