<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock;

use Magestore\InventorySuccess\Model\TransferStock as TransferStockModel;
use \Magestore\InventorySuccess\Model\TransferStock\TransferActivity as TransferActivityModel;
use \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct as TransferStockProductResource;
use Magestore\InventorySuccess\Api\Data\TransferStock\TransferStockInterface;

class ReturnStock extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\AbstractTransfer
{
    /**
     * @var \Magestore\InventorySuccess\Model\TransferStock\TransferActivityFactory
     */
    protected $_transferActivityFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\TransferStock\TransferActivityProductFactory
     */
    protected $_transferActivityProductFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    protected $adminSession;

    /** @var  \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProductFactory */
    protected $_transferStockProductResourceFactory;

    /** @var \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagementFactory */
    protected $_transferStockManagement;

    /** @var  \Magestore\InventorySuccess\Model\Locator\LocatorFactory */
    protected $_locatorFactory;

    /** @var  \Magestore\InventorySuccess\Model\TransferStock\Email\EmailNotificationFactory */
    protected $_emailNotificationFactory;

    
    /**
     * Save constructor.
     * @param \Magestore\InventorySuccess\Controller\Adminhtml\Context $context
     * @param TransferStockModel\TransferActivityFactory $transferActivityFactory
     * @param TransferStockModel\TransferActivityProductFactory $transferActivityProductFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Backend\Model\Auth\Session $adminSession
     */
    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProductFactory $transferStockProductResourceFactory,
        \Magestore\InventorySuccess\Model\TransferStock\TransferActivityProductFactory $transferActivityProductFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Backend\Model\Auth\Session $adminSession,
        \Magestore\InventorySuccess\Model\Locator\LocatorFactory $locatorFactory,
        \Magestore\InventorySuccess\Model\TransferStock\Email\EmailNotificationFactory $emailNotificationFactory
    ) {
        parent::__construct($context);
        $this->_transferActivityFactory = $context->getTransferActivityFactory();
        $this->_transferActivityProductFactory = $transferActivityProductFactory;
        $this->timezone = $timezone;
        $this->adminSession = $adminSession;
        $this->_transferStockProductResourceFactory = $transferStockProductResourceFactory;
        $this->_transferStockManagement = $context->getTransferStockManagementFactory();
        $this->_emailNotificationFactory = $emailNotificationFactory;

    }

    public function execute()
    {
        $dataSession = $this->_objectManager->get('\Magento\Newsletter\Model\Session');
        $return_products = $dataSession->getData('return_product_data',true);

        $this->saveReturnTransferActivityProduct($return_products, TransferActivityModel::ACTIVITY_TYPE_RETURN);
        $this->messageManager->addSuccessMessage(__('Return successfully!'));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');
        $emailNotification = $this->_emailNotificationFactory->create();
        $emailNotification->notifyReturn($id);
        if($this->getRequest()->getParam('type') == TransferStockModel::TYPE_REQUEST ){
            return $resultRedirect->setPath('*/transferstock_request/edit',['id' => $id, '_current' => true]);
        }
        if($this->getRequest()->getParam('type') == TransferStockModel::TYPE_SEND ){
            return $resultRedirect->setPath('*/transferstock_send/edit',['id' => $id, '_current' => true]);
        }
    }

    /**
     * @param $activity_products
     * @param $activity_type
     */
    public function saveReturnTransferActivityProduct($activity_products, $activity_type){
        /* create Send Stock */
        $transferstock = $this->_transferStockFactory->create();
        $transferstock->load($this->getRequest()->getParam('id'));
        $sendStockActivity = $this->createSendStockActivity($activity_products,$transferstock);
        $transferActivity = $this->createTransferActivity($activity_products,$activity_type , $sendStockActivity, true);
        $activityId = $transferActivity->getActivityId();
        if($activityId){
            //reformat delivery product data
            $products = [];
            foreach ($activity_products as $item){
                $product = [];
                $product['activity_id'] = $activityId;
                $product['product_id'] = $item['id'];
                $product['product_name'] = $item['name'];
                $product['product_sku'] = $item['sku'];
                $product['qty'] =  $item['qty'];
                $products[$item['id']] = $product;
            }
            /** @var \Magestore\InventorySuccess\Model\TransferStock\TransferActivityManagement $transferActivityManagement */
            $transferActivityManagement = $this->_objectManager->create('\Magestore\InventorySuccess\Model\TransferStock\TransferActivityManagement');
            $transferActivityManagement->setProducts($transferActivity, $products);
            $transferstockId = $this->getRequest()->getParam('id');
            $transferActivityManagement->updateTransferstockProductQtySummary($transferstockId, $activity_products, $activity_type);
        }

    }

    /**
     * @param $activity_products
     * @return mixed
     */
    public function createSendStockActivity($activity_products,$transferStock){
        /** @var \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement $transferStockManagement */
        $transferStockManagement = $this->_objectManager->create('\Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement');
        $code = $transferStockManagement->generateCode();
        $data = array();
        $data['transferstock_code'] = $code;
        $data['notifier_emails'] = '';
        $data['reason'] = 'Return stock form '. $transferStock->getTransferstockCode();
        $data['source_warehouse_id'] = $transferStock->getDesWarehouseId();
        $data['des_warehouse_id'] = $transferStock->getSourceWarehouseId();
        $data['status'] = TransferStockInterface::STATUS_PROCESSING;
        $data['action'] = 'prepare_product';
        $adminUser = $this->adminSession->getUser();
        if ($adminUser->getId()) {
            $adminName = $adminUser->getUserName();
        } else {
            $adminName = '';
        }
        if(isset($data['source_warehouse_id'])){
            $data['source_warehouse_code'] = $this->getWarehouseCode($data['source_warehouse_id']);
        }
        if(isset($data['des_warehouse_id'])){
            $data['des_warehouse_code'] = $this->getWarehouseCode($data['des_warehouse_id']);
        }
        $data['type'] = TransferStockModel::TYPE_SEND;
        $data['created_by'] = $adminName;
        $data['created_at'] = strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp());
        $transferstock = $this->_transferStockFactory->create();
        //update transfer stock information
        $transferstock->setData($data);
        try {
            $transferstock->save();
            $data = $this->reformatPostDataForReturn($activity_products,$transferstock->getId());
            $transferStockManagement->saveTransferStockProduct($transferstock->getId(), $data);
            return $transferstock->getTransferstockCode();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the page.'));
        }
    }

    /**
     * @param $activity_products
     * @param $id
     * @return array
     */
    public function reformatPostDataForReturn($activity_products, $id){
        $newData = [];
        foreach ($activity_products as $index => $value){
            $item = [];
            $item['transferstock_id'] = $id;
            $item['product_id'] = $value['id'];
            $item['product_name'] = $value['name'];
            $item['product_sku'] = $value['sku'];
            $item['qty'] =  $value['qty'];
            $newData[$value['id']] = $item;
        }
        return $newData;
    }


    /**
     * create new delivery
     */
    public function createTransferActivity($activity_products, $activity_type, $id = null, $send_stock = false){

        $adminUser = $this->adminSession->getUser();
        if ($adminUser->getId()) {
            $adminName = $adminUser->getUserName();
        } else {
            $adminName = '';
        }
        $data = [];
        $data['activity_type'] = $activity_type;
        $data['created_by'] = $adminName;
        $data['created_at'] = strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp());
        $data['transferstock_id'] = $this->getRequest()->getParam('id');
        $data['total_qty'] = $this->getTransferActivityTotalQty($activity_products);
        if($send_stock == true){
            $data['note'] = 'return by new Transfer stock '.$id;
        }
        $transferActivity = $this->_transferActivityFactory->create();
        $transferActivity->setData($data);

        try {
            $transferActivity->save();
            return $transferActivity;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the page.'));
        }

        return 0;
    }

    /**
     * @param $warehouseId
     * @return string
     */
    public function getWarehouseCode($warehouseId){
        /** @var \Magestore\InventorySuccess\Model\Warehouse $warehouse */
        $warehouse = $this->_warehouseFactory->create()->load($warehouseId);
        return $warehouse->getWarehouseCode();
    }
    /**
     * count total qty of products in a delivery
     * @param $delivery_products
     * @return int
     */
    public function getTransferActivityTotalQty($activity_products){
        $total_qty = 0;
        foreach ($activity_products as $product){
            $total_qty = $total_qty + $product['qty'];
        }
        return $total_qty;
    }



}