<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\Rule;

class Edit extends \Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\AbstractLowStockNotification
{
    const ADMIN_RESOURCE = 'Magestore_InventorySuccess::view_notification_rule';
    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            try {
                $model = $this->_objectManager->create('Magestore\InventorySuccess\Model\LowStockNotification\Rule')->load($id);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
                $this->messageManager->addError(__('This rule no longer exists.'));
                $this->_redirect('*/*/*');
                return;
            }
        } else {
            /** @var \Magestore\InventorySuccess\Model\LowStockNotification\Rule $model */
            $model = $this->_objectManager->create('Magestore\InventorySuccess\Model\LowStockNotification\Rule');
        }

        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $model->getConditions()->setFormName('os_lowstock_notification_rule_form');
        $model->getConditions()->setJsFormObject(
            $model->getConditionsFieldSetId($model->getConditions()->getFormName())
        );

        $this->_coreRegistry->register('current_lowstock_notification_rule', $model);

        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magestore_InventorySuccess::list_notification_rule'
        )->_addBreadcrumb(
            __('Inventory Success'),
            __('Manage Low Stock Notification Rules')
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Low Stock Notification Rule'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(
            $model->getRuleId() ? $model->getRuleName() : __('New Low Stock Notification Rule')
        );

        $breadcrumb = $id ? __('Edit Rule') : __('New Rule');
        $this->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->_view->renderLayout();
    }
}
