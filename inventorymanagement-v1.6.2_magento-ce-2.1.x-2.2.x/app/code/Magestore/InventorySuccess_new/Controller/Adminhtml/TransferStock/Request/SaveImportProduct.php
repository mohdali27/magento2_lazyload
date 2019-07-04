<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\Request;
use Magento\Framework\Controller\ResultFactory;

class SaveImportProduct extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\AbstractRequest
{

    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                $importHandler = $this->_objectManager->create('Magestore\InventorySuccess\Model\TransferStock\Import\CsvRequestImportHandler');
                $data = $importHandler->importFromCsvFile($this->getRequest()->getFiles('import_product_request'));
                $this->saveTransferStockProduct($data);
                $this->messageManager->addSuccessMessage(__('The product adjust has been imported.'));

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Invalid file upload attempt'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Invalid file upload attempt'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
        return $resultRedirect;
    }

    public function saveTransferStockProduct($data){
        $id = $this->getRequest()->getParam('id');
        $transferStock = $this->_objectManager->create('\Magestore\InventorySuccess\Model\TransferStock');
        $transferStock->load($id);
        /** @var \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement $transferStockManagement */
        $transferStockManagement = $this->_objectManager->create('\Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement');
        $transferStockManagement->setProducts($transferStock, $data);
    }
}


