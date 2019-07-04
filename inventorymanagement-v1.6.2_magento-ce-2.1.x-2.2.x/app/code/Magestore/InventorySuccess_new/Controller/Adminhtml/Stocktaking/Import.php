<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Import
 * @package Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking
 */
class Import extends \Magestore\InventorySuccess\Controller\Adminhtml\Stocktaking\Stocktaking
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                /** @var $importHandler \Magento\TaxImportExport\Model\Rate\CsvImportHandler */
                $status = $this->getRequest()->getParam('status');
                $importHandler = $this->_objectManager
                                ->create('Magestore\InventorySuccess\Model\Stocktaking\CsvImportHandler');
                $importHandler->importFromCsvFile($this->getRequest()->getFiles('import_product'), $status);
                $this->messageManager->addSuccessMessage(__('The product adjust has been imported.'));
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
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
}
