<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\Activity;

use \Magestore\InventorySuccess\Api\Data\TransferStock\TransferActivityInterface;
class View extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\AbstractRequest
{
    /**
     * @var \Magestore\InventorySuccess\Model\TransferStock\TransferActivityFactory
     */
    protected $_transferActivityFactory;

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

    /** @var  \Magestore\InventorySuccess\Model\Locator\LocatorFactory $_locatorFactory */
    protected $_locatorFactory;

    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Backend\Model\Auth\Session $adminSession,
        \Magestore\InventorySuccess\Model\Locator\LocatorFactory $locatorFactory

    ){
        parent::__construct($context);
        $this->_transferActivityFactory = $context->getTransferActivityFactory();
        $this->_adminSession = $adminSession;
        $this->_locatorFactory = $locatorFactory;

    }
    public function execute()
    {

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->_request->getParam("id");
        /** @var \Magestore\InventorySuccess\Model\Locator\Locator $locator */
        $locator = $this->_locatorFactory->create();
        $locator->setSessionByKey("current_activity_id", $id);
        $transferActivity = $this->_transferActivityFactory->create();
        if($id){
            $transferActivity->load($id);
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Magestore_InventorySuccess::inventory');

        if($transferActivity->getActivityType() == TransferActivityInterface::ACTIVITY_TYPE_DELIVERY){
            $pageTitle = "View Delivery#" . $id;
        }
        elseif($transferActivity->getActivityType() == TransferActivityInterface::ACTIVITY_TYPE_RETURN){
            $pageTitle = "View Return#" . $id;
        }
        else{
            $pageTitle = "View Receiving#" . $id;
        }

        $resultPage->getConfig()->getTitle()->prepend(__($pageTitle));

        return $resultPage;
    }

}


