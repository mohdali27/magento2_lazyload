<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking;

/**
 * Class Import
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking
 */
class Adjust extends \Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking\Stocktaking
{
    const SAMPLE_QTY = 1;

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getNewAdjustStockData();
        if (count($data)) {
            try {
                $adjustStock = $this->_objectManager
                    ->create('Magestore\InventorySuccess\Model\AdjustStock');
                $adjustStockManagement = $this->_objectManager
                    ->create('Magestore\InventorySuccess\Model\AdjustStock\AdjustStockManagement');
                $adjustData = $data;
                $adjustData['products'] = [];
                if (count($this->getAdjustProductCollection())) {
                    $adjustData['products'] = $this->getAdjustProductCollection();
                }
                $adjustStockManagement->createAdjustment($adjustStock, $adjustData);
                /* if created adjuststock then complete it */
                if ($adjustStock->getId()) {
                    $this->messageManager->addSuccessMessage(__('A new adjustment has been created.'));
                    return $resultRedirect->setPath('inventorysuccess/adjuststock/edit', ['id' => $adjustStock->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                if ($this->getRequest()->getParam('id')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                }
            }
        }
        $this->messageManager->addErrorMessage(
            __('Unable to find adjust stock to create')
        );
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * get new adjust stock data
     *
     * @param
     * @return array
     */
    public function getNewAdjustStockData()
    {
        $id = $this->getRequest()->getParam('id');
        $warehouseId = $this->getRequest()->getParam('warehouse_id');
        $stocktakingCode = $this->getRequest()->getParam('stocktaking_code');
        $data = array();
        if (isset($id)) {
            $data['warehouse_id'] = $warehouseId;
            $data['reason'] = __('Adjust stock from stocktaking %1', $stocktakingCode);
        }
        return $data;
    }

    /**
     * get different product collection
     *
     * @param
     * @return array
     */
    public function getAdjustProductCollection()
    {
        $stocktakingId = $this->getRequest()->getParam('id');
        $data = array();
        if (isset($stocktakingId)) {
            $stocktakingkManagement = $this->stocktakingManagement;
            $stocktaking = $this->stocktakingFactory->create();
            $stocktaking->setId($stocktakingId);
            $productCollection = $stocktakingkManagement->getDifferentProducts($stocktaking);
            $data = $this->prepareData($productCollection);
        }
        return $data;
    }

    /**
     * prepare stocktaking data
     *
     * @param
     * @return array
     */
    public function prepareData($productCollection)
    {
        $data = array();
        foreach ($productCollection as $productModel) {
            $data[$productModel->getProductId()] = array(
                'product_name' => $productModel->getData('product_name'),
                'product_sku' => $productModel->getData('product_sku'),
                'adjust_qty' => $productModel->getData('stocktaking_qty')
            );
        }
        return $data;
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magestore_InventorySuccess::create_adjuststock');
    }

}
