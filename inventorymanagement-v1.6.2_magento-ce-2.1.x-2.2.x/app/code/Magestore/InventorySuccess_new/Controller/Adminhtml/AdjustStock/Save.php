<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\AdjustStock;

use Magestore\InventorySuccess\Api\Data\AdjustStock\AdjustStockInterface;

/**
 * Class Save
 * @package Magestore\InventorySuccess\Controller\Adminhtml\AdjustStock
 */
class Save extends \Magestore\InventorySuccess\Controller\Adminhtml\AdjustStock\AdjustStock
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data = $this->getRequest()->getPostValue()) {
            try {
                $adjustStock = $this->adjustStockFactory->create();
                $adjustData = $this->getAdjustData($data);
                if (isset($data[AdjustStockInterface::ADJUSTSTOCK_ID]))
                    $adjustStock->setId($data[AdjustStockInterface::ADJUSTSTOCK_ID]);
                $adjustData['products'] = [];
                if (isset($data['links'])) {
                    if (is_string($data['links'])) {
                        $data['links'] = json_decode($data['links'], true);
                    }
                    $adjustData['products'] = $this->getProducts($data['links']);
                }
                $this->adjustStockManagement->createAdjustment($adjustStock, $adjustData);
                /* if created adjuststock then complete it */
                if ($adjustStock->getId()) {
                    if ($this->getRequest()->getParam('back') == 'confirm') {
                        if (count($adjustData['products']) <= 0) {
                            $this->messageManager->addErrorMessage(__('No product to adjust stock.'));
                            return $resultRedirect->setPath('*/*/edit', ['id' => $adjustStock->getId()]);
                        }
                        $this->adjustStockManagement->complete($adjustStock);
                        $this->messageManager->addSuccessMessage(__('The adjustment has been confirmed.'));
                        return $resultRedirect->setPath('*/*/edit', ['id' => $adjustStock->getId()]);
                    }
                    $this->messageManager->addSuccessMessage(__('The adjustment has been saved.'));
                    if ($this->getRequest()->getParam('back') == 'edit') {
                        return $resultRedirect->setPath('*/*/edit', ['id' => $adjustStock->getId()]);
                    }
                }

                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back') == 'new') {
                    return $resultRedirect->setPath('*/*/new');
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Adjustment code was existed.'));
                $this->_getSession()->setFormData($data);
                if (isset($data['adjuststock_id'])) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $data['adjuststock_id']]);
                }
                return $resultRedirect->setPath('*/*/new');
            }
        }
        $this->messageManager->addErrorMessage(
            __('Unable to find adjust stock to create')
        );
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * get products to adjust stock
     *
     * @param array
     * @return array
     */
    public function getProducts($dataLinks)
    {
        $products = [];
        if (isset($dataLinks['product_list'])) {
            if ($this->helper->getAdjustStockChange()) {
                foreach ($dataLinks['product_list'] as $product) {
                    $adjustQty = $product['change_qty'] + $product['total_qty'];
                    $products[$product['id']] = [
                        'adjust_qty' => $adjustQty,
                        'product_sku' => $product['sku'],
                        'product_name' => $product['name'],
                        'old_qty' => $product['total_qty'],
                        'change_qty' => $product['change_qty']
                    ];
                }
            } else {
                foreach ($dataLinks['product_list'] as $product) {
                    $changeQty = $product['adjust_qty'] - $product['total_qty'];
                    $products[$product['id']] = [
                        'adjust_qty' => $product['adjust_qty'],
                        'product_sku' => $product['sku'],
                        'product_name' => $product['name'],
                        'old_qty' => $product['total_qty'],
                        'change_qty' => $changeQty
                    ];
                }
            }
        }
        return $products;
    }

    /**
     * get adjust stock data
     *
     * @param array
     * @return array
     */
    public function getAdjustData($data)
    {
        $adjustData = [];
        $adjustData[AdjustStockInterface::ADJUSTSTOCK_CODE] = isset($data[AdjustStockInterface::ADJUSTSTOCK_CODE]) ?
            $data[AdjustStockInterface::ADJUSTSTOCK_CODE] :
            null;
        $adjustData[AdjustStockInterface::WAREHOUSE_ID] = isset($data[AdjustStockInterface::WAREHOUSE_ID]) ?
            $data[AdjustStockInterface::WAREHOUSE_ID] :
            null;
        $adjustData[AdjustStockInterface::WAREHOUSE_CODE] = isset($data[AdjustStockInterface::WAREHOUSE_CODE]) ?
            $data[AdjustStockInterface::WAREHOUSE_CODE] :
            null;
        $adjustData[AdjustStockInterface::WAREHOUSE_NAME] = isset($data[AdjustStockInterface::WAREHOUSE_NAME]) ?
            $data[AdjustStockInterface::WAREHOUSE_NAME] :
            null;
        $adjustData[AdjustStockInterface::REASON] = isset($data[AdjustStockInterface::REASON]) ?
            $data[AdjustStockInterface::REASON] :
            '';
//        $adjustData[AdjustStockInterface::CREATED_AT] = isset($data[AdjustStockInterface::CREATED_AT]) ?
//            $data[AdjustStockInterface::CREATED_AT] :
//            $this->systemHelper->getCurTime();
//        $adjustData[AdjustStockInterface::CREATED_BY] = isset($data[AdjustStockInterface::CREATED_BY]) ?
//            $data[AdjustStockInterface::CREATED_BY] :
//            $this->systemHelper->getCurUser()->getUserName();

        return $adjustData;
    }

}
