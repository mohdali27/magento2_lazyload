<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\Rule;

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

    const ADMIN_RESOURCE = 'Magestore_InventorySuccess::list_notification_rule';

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
        /** @var \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Rule\Collection $ruleCollection */
        $ruleCollection = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Rule\CollectionFactory'
        )->create()->addFieldToFilter('apply', \Magestore\InventorySuccess\Model\LowStockNotification\Rule::NOT_APPLY)
            ->addFieldToFilter('status', \Magestore\InventorySuccess\Model\LowStockNotification\Rule::STATUS_ACTIVE);
        if ($ruleCollection->getSize()) {
            $this->messageManager->addNoticeMessage(
                __('We found updated rules that are not applied. Please click "Apply Rules" to update your rule.')
            );
        }
        $resultPage->setActiveMenu('Magestore_InventorySuccess::list_notification_rule');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Low Stock Notification Rules'));
        $resultPage->addBreadcrumb(__('Inventory Success'), __('Inventory Success'));
        $resultPage->addBreadcrumb(__('Manage Low Stock Notification Rules'), __('Manage Low Stock Notification Rules'));

        return $resultPage;
    }
}