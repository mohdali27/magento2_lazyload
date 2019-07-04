<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\AdjustStock;

/**
 * Class Edit
 * @package Magestore\InventorySuccess\Controller\Adminhtml\AdjustStock
 */
class Edit extends \Magestore\InventorySuccess\Controller\Adminhtml\AdjustStock\AdjustStock
{

    /**
     * Edit Store.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $id = $this->getRequest()->getParam('id');
        /** @var \Magestore\InventorySuccess\Model\AdjustStock $model */
        $model = $this->adjustStockFactory->create();
        if ($id) {
            $this->adjustStockResource->load($model, $id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This adjustment no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('current_adjuststock', $model);
        
        if($model->getWarehouseId()) {
            /* register current warehouse */
            $warehouse = $this->warehouseFactory->create()->load($model->getWarehouseId());
            $this->_coreRegistry->register('current_warehouse', $warehouse);
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();

        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Adjustment') : __('Add a New Adjustment'),
            $id ? __('Edit Adjustment') : __('Add a New Adjustment')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Adjustment'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ?
                __('Edit Adjustment "%1"', $model->getAdjustStockCode()) : __('Add New Adjustment')
        );

        return $resultPage;
    }

    /**
     * Init page.
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Magestore_InventorySuccess::adjuststock_history')
            ->addBreadcrumb(__('InventorySuccess'), __('InventorySuccess'))
            ->addBreadcrumb(__('Manage Adjustment'), __('Edit Adjustment'));

        return $resultPage;
    }
}
