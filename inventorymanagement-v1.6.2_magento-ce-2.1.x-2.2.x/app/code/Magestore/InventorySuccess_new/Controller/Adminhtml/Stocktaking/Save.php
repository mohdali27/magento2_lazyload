<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking;

use Magestore\InventorySuccess\Api\Data\Stocktaking\StocktakingInterface;

/**
 * Class Save
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking
 */
class Save extends \Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking\Stocktaking
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        $this->modifyDateParams($data);
        if ($data) {
            try {
                $stocktaking = $this->stocktakingFactory->create();
                $stocktakingData = $this->getStocktakingData($data);
                if (isset($data[StocktakingInterface::STOCKTAKING_ID])) {
                    $stocktaking->setId($data[StocktakingInterface::STOCKTAKING_ID]);
                }
                $stocktakingData['products'] = [];
                if (isset($data['links'])) {
                    if (is_string($data['links'])) {
                        $data['links'] = json_decode($data['links'], true);
                    }
                    $stocktakingData['products'] = $this->getProducts($data['links']);
                }
                if (count($stocktakingData['products']) <= 0) {
                    if ($this->getRequest()->getParam('back') == 'confirm' ||
                        $this->getRequest()->getParam('back') == 'verify'
                    ) {
                        $this->messageManager->addErrorMessage(__('No product to stocktake.'));
                        return $resultRedirect->setPath('*/*/edit', ['id' => $stocktaking->getId()]);
                    }
                }
                $this->stocktakingManagement->createStocktaking($stocktaking, $stocktakingData);
                /* if created stocktaking then complete it */
                if ($stocktaking->getId()) {
                    if ($this->getRequest()->getParam('back') == 'cancel') {
                        $this->messageManager->addSuccessMessage(__('The stocktaking has been canceled.'));
                        return $resultRedirect->setPath('*/*/cancel', ['id' => $stocktaking->getId()]);
                    }
                    if ($this->getRequest()->getParam('back') == 'reopen') {
                        $this->messageManager->addSuccessMessage(__('The stocktaking has been re-open.'));
                        return $resultRedirect->setPath('*/*/reopen', ['id' => $stocktaking->getId()]);
                    }
                    if ($this->getRequest()->getParam('back') == 'delete') {
                        $this->messageManager->addSuccessMessage(__('The stocktaking has been deleted.'));
                        return $resultRedirect->setPath('*/*/delete', ['id' => $stocktaking->getId()]);
                    }

                    if ($this->getRequest()->getParam('back') == 'confirm') {
                        $this->messageManager->addSuccessMessage(__('The stocktaking has been completed.'));
                        return $resultRedirect->setPath('*/*/edit', ['id' => $stocktaking->getId()]);
                    }
                    $this->messageManager->addSuccessMessage(__('The stocktaking has been saved.'));

                    if ($this->getRequest()->getParam('back') == 'new') {
                        return $resultRedirect->setPath('*/*/new');
                    }

                    if ($this->getRequest()->getParam('back') != 'close') {
                        return $resultRedirect->setPath('*/*/edit', ['id' => $stocktaking->getId()]);
                    }
                }
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back') == 'new') {
                    return $resultRedirect->setPath('*/*/new');
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // $this->messageManager->addErrorMessage($e->getMessage());
                $this->messageManager->addErrorMessage(__('Stocktaking code was existed.'));
                $this->_getSession()->setFormData($data);
                if (isset($data['stocktaking_id'])) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $data['stocktaking_id']]);
                }
                return $resultRedirect->setPath('*/*/new');
            }
        }
        $this->messageManager->addErrorMessage(
            __('Unable to find stocktaking stock to create')
        );
        return $resultRedirect->setPath('*/*/');
    }

    protected function modifyDateParams(&$params) {
        $dateField = ['stocktake_at'];
        $dateFormat = $this->timezone->getDateFormat();
        foreach ($dateField as $field) {
            if(!isset($params[$field])) {
                continue;
            }
            $dateData = $params[$field];
            if(substr($dateFormat, 0, 1) == 'd' || substr($dateFormat, 0, 1) == 'D') {
                $dateData = str_replace('/','-',$params[$field]);
            }
            $params[$field] = $this->timezone->convertConfigTimeToUtc($dateData);
        }
    }

    /**
     * get products to stocktaking stock
     *
     * @param array
     * @return array
     */
    public function getProducts($dataLinks)
    {
        $products = [];
        if (isset($dataLinks['product_list'])) {
            foreach ($dataLinks['product_list'] as $product) {
                $products[$product['id']] = ['product_sku' => $product['sku'],
                    'product_name' => $product['name'],
                    'old_qty' => $product['total_qty'],
                    'stocktaking_qty' => $product['stocktaking_qty'],
                    'stocktaking_reason' => isset($product['stocktaking_reason']) ? $product['stocktaking_reason'] : '',
                ];
            }
        }
        return $products;
    }

    /**
     * get stocktaking stock data
     *
     * @param array
     * @return array
     */
    public function getStocktakingData($data)
    {
        $stocktakingData = [];
        $stocktakingData[StocktakingInterface::STOCKTAKING_CODE] = isset($data[StocktakingInterface::STOCKTAKING_CODE]) ?
            $data[StocktakingInterface::STOCKTAKING_CODE] :
            null;
        $stocktakingData[StocktakingInterface::WAREHOUSE_ID] = isset($data[StocktakingInterface::WAREHOUSE_ID]) ?
            $data[StocktakingInterface::WAREHOUSE_ID] :
            null;
        $stocktakingData[StocktakingInterface::WAREHOUSE_CODE] = isset($data[StocktakingInterface::WAREHOUSE_CODE]) ?
            $data[StocktakingInterface::WAREHOUSE_CODE] :
            null;
        $stocktakingData[StocktakingInterface::WAREHOUSE_NAME] = isset($data[StocktakingInterface::WAREHOUSE_NAME]) ?
            $data[StocktakingInterface::WAREHOUSE_NAME] :
            null;
        $stocktakingData[StocktakingInterface::REASON] = isset($data[StocktakingInterface::REASON]) ?
            $data[StocktakingInterface::REASON] :
            '';
        $stocktakingData[StocktakingInterface::PARTICIPANTS] = isset($data[StocktakingInterface::PARTICIPANTS]) ?
            $data[StocktakingInterface::PARTICIPANTS] :
            '';
        $stocktakingData[StocktakingInterface::STOCKTAKE_AT] = isset($data[StocktakingInterface::STOCKTAKE_AT]) ?
            $data[StocktakingInterface::STOCKTAKE_AT] :
            null;
        $stocktakingData[StocktakingInterface::VERIFIED_BY] = isset($data[StocktakingInterface::VERIFIED_BY]) ?
            $data[StocktakingInterface::VERIFIED_BY] :
            null;
        $stocktakingData[StocktakingInterface::VERIFIED_AT] = isset($data[StocktakingInterface::VERIFIED_AT]) ?
            $data[StocktakingInterface::VERIFIED_AT] :
            null;
        $stocktakingData[StocktakingInterface::CONFIRMED_BY] = isset($data[StocktakingInterface::CONFIRMED_BY]) ?
            $data[StocktakingInterface::CONFIRMED_BY] :
            null;
        $stocktakingData[StocktakingInterface::CONFIRMED_AT] = isset($data[StocktakingInterface::CONFIRMED_AT]) ?
            $data[StocktakingInterface::CONFIRMED_AT] :
            null;
        $stocktakingData[StocktakingInterface::STATUS] = isset($data[StocktakingInterface::STATUS]) ?
            $data[StocktakingInterface::STATUS] :
            0;

        if ($this->getRequest()->getParam('back') == 'cancel') {
            $stocktakingData[StocktakingInterface::STATUS] = StocktakingInterface::STATUS_CANCELED;
        }
        if ($this->getRequest()->getParam('back') == 'reopen') {
            $stocktakingData[StocktakingInterface::STATUS] = StocktakingInterface::STATUS_PENDING;
        }

        if ($this->getRequest()->getParam('back') == 'start'
            || $this->getRequest()->getParam('back') == 'redata'
        ) {
            $stocktakingData[StocktakingInterface::STATUS] = StocktakingInterface::STATUS_PROCESSING;
        }
        if ($this->getRequest()->getParam('back') == 'verify') {
            $stocktakingData[StocktakingInterface::STATUS] = StocktakingInterface::STATUS_VERIFIED;
            $stocktakingData[StocktakingInterface::VERIFIED_BY] = $this->systemHelper->getCurUser()->getUserName();
            $stocktakingData[StocktakingInterface::VERIFIED_AT] = $this->systemHelper->getCurTime();
        }
        if ($this->getRequest()->getParam('back') == 'confirm') {
            $stocktakingData[StocktakingInterface::STATUS] = StocktakingInterface::STATUS_COMPLETED;
            $stocktakingData[StocktakingInterface::CONFIRMED_BY] = $this->systemHelper->getCurUser()->getUserName();
            $stocktakingData[StocktakingInterface::CONFIRMED_AT] = $this->systemHelper->getCurTime();
        }

        return $stocktakingData;
    }

}
