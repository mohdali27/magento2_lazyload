<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\Send;

use Magestore\InventorySuccess\Model\TransferStock;

class Edit extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\AbstractRequest
{

    /**
     * @var \Magestore\InventorySuccess\Model\TransferStockFactory
     */
    protected $_transferStockFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_adminSession;

    /**
     * @var \Magestore\InventorySuccess\Model\Warehouse\PermissionFactory
     */
    protected $_warehousePermissionFactory;

    /**
     * @var  \Magestore\InventorySuccess\Model\Locator\LocatorFactory
     */
    protected $_locatorFactory;

    /**
     * @var TransferStock\ShortfallValidation
     */
    protected $shortfallValidation;


    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Backend\Model\Auth\Session $adminSession,
        \Magestore\InventorySuccess\Model\Locator\LocatorFactory $locatorFactory,
        \Magestore\InventorySuccess\Model\TransferStock\ShortfallValidation $shortfallValidation

    )
    {
        parent::__construct($context);
        $this->_transferStockFactory = $context->getTransferStockFactory();
        $this->_adminSession = $adminSession;
        $this->_locatorFactory = $locatorFactory;
        $this->shortfallValidation = $shortfallValidation;
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');

        /** @var \Magestore\InventorySuccess\Model\TransferStock $model */
        $model = $this->_transferStockFactory->create();

        if ($id) {
            /* notice shortfall */
            $this->shortfallValidation->_showNoticeShortfall($id, TransferStock::TYPE_SEND);

            $model->load($id);

            if (!$model->getTransferstockId()) {
                $this->messageManager->addError(__('This transferstock is no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        
        $this->_coreRegistry->register('current_transferstock', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Transfer Stock') : __('Add a New Send Stock'),
            $id ? __('Transfer Stock') : __('Add a New Send Stock')
        );

        $resultPage->getConfig()->getTitle()->prepend(
            $model->getId() ?
                __('Send Stock "%1"', $model->getTransferstockCode()) : __('Add a New Send Stock')
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
        $resultPage->setActiveMenu('Magestore_InventorySuccess::send_stock_create')
            ->addBreadcrumb(__('InventorySuccess'), __('InventorySuccess'))
            ->addBreadcrumb(__('Manage Transfer Stock'), __('Manage Transfer Stock'));

        return $resultPage;
    }

}


