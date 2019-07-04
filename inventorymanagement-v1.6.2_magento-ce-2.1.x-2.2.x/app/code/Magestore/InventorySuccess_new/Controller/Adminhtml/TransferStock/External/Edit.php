<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\External;

use Magestore\InventorySuccess\Api\Data\TransferStock\TransferPermission;

class Edit extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\AbstractRequest
{

    const ADMIN_RESOURCE = TransferPermission::EXTERNAL_TRANSFER_STOCK_VIEW;

    /**
     * @var \Magestore\InventorySuccess\Model\TransferStockFactory
     */
    protected $_transferStockFactory;

    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context

    )
    {
        parent::__construct($context);
        $this->_transferStockFactory = $context->getTransferStockFactory();

    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');

        $transferstockCode = "";
        /** @var \Magestore\InventorySuccess\Model\TransferStock $model */
        $model = $this->_transferStockFactory->create();
        if ($id) {
            $model->load($id);
            if (!$model->getTransferstockId()) {
                $this->messageManager->addError(__('This transferstock is no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
            $transferstockCode = "#" . $model->getTransferstockCode();
        }

        $this->_coreRegistry->register('current_transferstock', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Magestore_InventorySuccess::transfer_to_external_create');
        $type = $this->getRequest()->getParam('type');
        if ($type == 'to_external') {
            $pageTitle = "Transfer Stock to External Location " . $transferstockCode;
        } else {
            $pageTitle = "Transfer Stock from External Location " . $transferstockCode;
        }
        $resultPage->getConfig()->getTitle()->prepend(__($pageTitle));

        return $resultPage;
    }

}


