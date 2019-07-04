<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking;

/**
 * Class Edit
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking
 */
class Edit extends \Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking\Stocktaking
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
        /** @var \Magestore\InventorySuccess\Model\Stocktaking $model */
        $model = $this->stocktakingFactory->create();
        if ($id) {
            $this->stocktakingResource->load($model, $id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This stocktaking no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('current_stocktaking', $model);

        if($model->getWarehouseId()) {
            /* register current warehouse */
            $warehouse = $this->warehouseFactory->create()->load($model->getWarehouseId());
            $this->_coreRegistry->register('current_warehouse', $warehouse);
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();

        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Stocktaking') : __('Add a New Stocktaking'),
            $id ? __('Edit Stocktaking') : __('Add a New Stocktaking')
        );

        $resultPage->getConfig()->getTitle()->prepend(__('Manage Stocktaking'));
        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ?
                __('Edit Stocktaking "%1"', $model->getStocktakingCode()) : __('Add New Stocktaking')
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
        $resultPage->setActiveMenu('Magestore_InventorySuccess::stocktaking_history')
            ->addBreadcrumb(__('InventorySuccess'), __('InventorySuccess'))
            ->addBreadcrumb(__('Manage Stocktaking'), __('Manage Stocktaking'));

        return $resultPage;
    }
}
