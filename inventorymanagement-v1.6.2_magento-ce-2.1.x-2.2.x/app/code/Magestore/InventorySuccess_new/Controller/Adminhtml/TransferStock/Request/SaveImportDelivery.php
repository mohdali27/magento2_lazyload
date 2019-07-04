<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\Request;
use Magento\Framework\Controller\ResultFactory;
use \Magestore\InventorySuccess\Model\TransferStock\TransferActivity as TransferActivityModel;
class SaveImportDelivery extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\Request\Save
{
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                $importHandler = $this->_objectManager->create('Magestore\InventorySuccess\Model\TransferStock\Import\CsvTransferImportHandler');
                $data = $importHandler->importFromCsvFile($this->getRequest()->getFiles('import_product'), \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_TRANSFER_STOCK_TO_TRANSFER_DELIVERY);
                //\Zend_Debug::dump($data);die();
                $this->saveTransferActivityProduct($data, TransferActivityModel::ACTIVITY_TYPE_DELIVERY);
                $this->messageManager->addSuccessMessage(__('The product transfer has been imported.'));

            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        } else {
            $this->messageManager->addErrorMessage(__('Invalid file upload attempt'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRedirectUrl());
        return $resultRedirect;
    }
}


