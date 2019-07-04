<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\External;
use Magento\Framework\Controller\ResultFactory;
use \Magestore\InventorySuccess\Model\TransferStock\TransferActivity as TransferActivityModel;
class SaveImportTransfer extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\External\Save
{
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                $importHandler = $this->_objectManager->create('Magestore\InventorySuccess\Model\TransferStock\Import\CsvTransferImportHandler');
                $type = $this->getRequest()->getParam('type');
                if ($type == 'to_external') {
                    $data = $importHandler->importFromCsvFile($this->getRequest()->getFiles('import_product'), \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_TRANSFER_STOCK_TO_TRANSFER_EXTERNAL_TO);
                } else {
                    $data = $importHandler->importFromCsvFile($this->getRequest()->getFiles('import_product'), \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_TRANSFER_STOCK_TO_TRANSFER_EXTERNAL_FROM);

                }
                //\Zend_Debug::dump($data);die();
                $this->saveTransferStockProduct($data, false);
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


