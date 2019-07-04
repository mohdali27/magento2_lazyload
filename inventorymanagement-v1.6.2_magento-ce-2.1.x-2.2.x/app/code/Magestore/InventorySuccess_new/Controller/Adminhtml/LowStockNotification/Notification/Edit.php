<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\Notification;

class Edit extends \Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\AbstractLowStockNotification
{
    const ADMIN_RESOURCE = 'Magestore_InventorySuccess::view_notification';

    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            try {
                $model = $this->_objectManager->create('Magestore\InventorySuccess\Model\LowStockNotification\Notification')->load($id);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addError(__('This notification no longer exists.'));
                $this->_redirect('*/*/*');
                return;
            }
        } else {
            $this->messageManager->addError(__('This notification no longer exists.'));
            $this->_redirect('*/*/*');
        }

        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->_coreRegistry->register('current_lowstock_notification', $model);
        /** @var  \Magestore\InventorySuccess\Model\Locator\Locator $locator */
        $locator = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\Locator\LocatorFactory'
        )->create();
        $locator->setSesionByKey('current_lowstock_notification', $model);
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magestore_InventorySuccess::list_notification'
        )->_addBreadcrumb(
            __('Inventory Success'),
            __('Low Stock Notifications')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Low Stock Notifications'));
        $this->_view->renderLayout();
    }
}
