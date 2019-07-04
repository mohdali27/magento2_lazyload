<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\Request;

use Magestore\InventorySuccess\Model\TransferStock;


class CreateDelivery extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\AbstractRequest
{



    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        $data = $this->getRequest()->getPostValue();
        //\Zend_Debug::dump($data);die();
        if(isset($data['links']['delivery_products'])) {
            $delivery_products = $data['links']['delivery_products'];
            //$this->saveTransferStockProduct($request_products);

        }

        //\Zend_Debug::dump($data);die();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $data['type'] = TransferStock::TYPE_REQUEST;
            $model = $this->_transferStockFactory->create();

            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }

            $model->setData($data);

            try {
                $model->save();
                return $resultRedirect->setPath('*/*/edit', ['id' => $model->getTransferstockId(), '_current' => true]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the page.'));
            }
        }
        return $resultRedirect->setPath('*/*/');
    }

    public function saveTransferStockProduct($data){
        $data = $this->reformatPostData($data);
        $id = $this->getRequest()->getParam('id');
        $transferStock = $this->_objectManager->create('\Magestore\InventorySuccess\Model\TransferStock');
        $transferStock->load($id);
        /** @var \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement $transferStockManagement */
        $transferStockManagement = $this->_objectManager->create('\Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement');

        $transferStockManagement->setProducts($transferStock, $data);
    }

    public function reformatPostData($data){
        $id = $this->getRequest()->getParam('id');
        $newData = [];

        foreach ($data as $index => $value){
            $item = [];
            $item['transferstock_id'] = $id;
            $item['product_id'] = $value['id'];
            $item['product_name'] = $value['name'];
            $item['product_sku'] = $value['sku'];
            $item['qty'] =  $value['request_qty'];

            $newData[$value['id']] = $item;
        }
        return $newData;
    }
}