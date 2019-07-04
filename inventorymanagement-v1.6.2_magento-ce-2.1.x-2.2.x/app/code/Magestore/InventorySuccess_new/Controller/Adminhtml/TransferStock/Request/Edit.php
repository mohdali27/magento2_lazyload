<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\Request;

use Magestore\InventorySuccess\Api\Data\TransferStock\TransferPermission;
use Magestore\InventorySuccess\Model\TransferStock;

class Edit extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\AbstractRequest
{
    const ADMIN_RESOURCE = TransferPermission::REQUEST_STOCK_VIEW;

    /**
     * @var TransferStock\ShortfallValidation
     */
    protected $shortfallValidation;
    
    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magestore\InventorySuccess\Model\TransferStock\ShortfallValidation $shortfallValidation
    ){
        parent::__construct($context);
        $this->shortfallValidation = $shortfallValidation;
    }
    
    public function execute()
    {

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        $this->_coreRegistry->register('transferstock_id', 1);

        /** @var \Magestore\InventorySuccess\Model\TransferStock $model */
        $model = $this->_transferStockFactory->create();
        if ($id) {
            /* notice shortfall */
            $this->shortfallValidation->_showNoticeShortfall($id,TransferStock::TYPE_REQUEST);

            $model->load($id);
            if (!$model->getTransferstockId()) {
                $this->messageManager->addError(__('This transferstock is no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }             
        }
        
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('current_transferstock', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Transfer Stock') : __('Add a New Request Stock'),
            $id ? __('Transfer Stock') : __('Add a New Request Stock')
        );

        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ?
                __('Request Stock "%1"', $model->getTransferstockCode()) : __('Add a New Request Stock')
        );

        return $resultPage;
    }

    /**
     * Init page.
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Magestore_InventorySuccess::request_stock_create')
            ->addBreadcrumb(__('InventorySuccess'), __('InventorySuccess'))
            ->addBreadcrumb(__('Manage Transfer Stock'), __('Manage Transfer Stock'));

        return $resultPage;
    }

}


