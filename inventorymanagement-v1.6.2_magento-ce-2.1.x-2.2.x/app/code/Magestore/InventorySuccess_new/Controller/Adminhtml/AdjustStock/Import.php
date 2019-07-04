<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\AdjustStock;
use Magento\Framework\Controller\ResultFactory;
/**
 * History Grid Holiday Action.
 *
 * @category Magestore
 * @package  Magestore_InventorySuccess
 * @module   Inventorysuccess
 * @author   Magestore Developer
 */
class Import extends \Magestore\InventorySuccess\Controller\Adminhtml\AdjustStock\AdjustStock
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                $import_immediately = $this->getRequest()->getParam('import_immediately');
                $importHandler = $this->_objectManager->create('Magestore\InventorySuccess\Model\AdjustStock\CsvImportHandler');
                $importHandler->importFromCsvFile($this->getRequest()->getFiles('import_product'),$import_immediately);
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
}
