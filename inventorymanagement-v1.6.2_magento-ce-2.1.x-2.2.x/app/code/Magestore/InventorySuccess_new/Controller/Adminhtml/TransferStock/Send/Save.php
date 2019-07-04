<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\Send;

use Magestore\InventorySuccess\Api\Data\TransferStock\TransferActivityInterface;
use Magestore\InventorySuccess\Model\TransferStock as TransferStockModel;
use Magestore\InventorySuccess\Api\Data\TransferStock\TransferStockInterface;
use \Magestore\InventorySuccess\Model\TransferStock\TransferActivity as TransferActivityModel;
use \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct as TransferStockProductResource;


class Save extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\AbstractTransfer
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

    /** @var  \Magestore\InventorySuccess\Model\Permission\PermissionManagement */
    protected $_permissionManagement;

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
    )
    {
        parent::__construct($context);
        $this->_transferActivityFactory = $context->getTransferActivityFactory();
        $this->_transferActivityProductFactory = $transferActivityProductFactory;
        $this->timezone = $timezone;
        $this->adminSession = $adminSession;
        $this->_transferStockProductResourceFactory = $transferStockProductResourceFactory;
        $this->_transferStockManagement = $context->getTransferStockManagementFactory();
        $this->_locatorFactory = $locatorFactory;
        $this->_emailNotificationFactory = $emailNotificationFactory;
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('id');
        $data = $this->getRequest()->getPostValue();
        $action = $this->getRequest()->getParam('action');
        $locator = $this->_locatorFactory->create();
        //check download action


        switch ($action) {
            case 'download_shortfall':
                return $resultRedirect->setPath('*/*/downloadshortfall/id/' . $id);
                break;
            case 'download_summary':
                return $resultRedirect->setPath('*/*/downloadsummary/id/' . $id);
                break;
        }

        if (!empty($data)) {
            if (isset($data['links']) && is_string($data['links'])) {
                $data['links'] = json_decode($data['links'], true);
            }
            $this->_coreRegistry->register("current_send_stock", $data);
        }

        //validate input data before do anything
        $transferStockManagement = $this->_transferStockManagement->create();
        $validateResult = $transferStockManagement->validate($data);

        if (!$validateResult['is_validate']) {
            foreach ($validateResult["errors"] as $error) {
                $this->messageManager->addErrorMessage(
                    __($error)
                );
            }

            return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
        } elseif ($data) {
            $transferstock = $this->_transferStockFactory->create();
            if ($id) {
                $transferstock->load($id);
                switch ($action) {
                    case "send_email":
                        return $resultRedirect->setPath('*/*/sendEmail/id/' . $id . '/whId/' . $data['des_warehouse_id']);
                        break;
                    case "cancel":
                        $transferstock->setData("status", TransferStockInterface::STATUS_CANCEL);
                        $this->messageManager->addSuccessMessage(__('The request #' . $transferstock->getTransferstockCode() . ' has been canceled !'));
                        break;
                    case "reopen":
                        $transferstock->setData("status", TransferStockInterface::STATUS_PENDING);
                        $this->messageManager->addSuccessMessage(__('The request #' . $transferstock->getTransferstockCode() . ' has been re-opened !'));
                        break;
                    case "delete":
                        $transferstock->delete();
                        $this->messageManager->addSuccessMessage(__('The request #' . $transferstock->getTransferstockCode() . ' has been deleted !'));
                        return $resultRedirect->setPath('*/transferstock_history/send');
                        break;
                    case "save_general":
                        $transferstock->setData("reason", $data['reason']);
                        $transferstock->setData("notifier_emails", $data['notifier_emails']);
                        $transferstock->setData("transferstock_code", $data['transferstock_code']);
                        $this->messageManager->addSuccessMessage(__('Saved general information successfully!'));
                        break;

                    case "save_product":
                        $send_products = [];
                        if (isset($data['links']['send_products'])) {
                            $send_products = $data['links']['send_products'];
                        }
                        $save_result = $this->saveTransferStockProduct($send_products, false, false);
                        if ($save_result) {
                            $this->messageManager->addSuccessMessage(__('Saved ' . count($send_products) . ' product(s) to the sending list!'));
                        } else {
                            $this->messageManager->addErrorMessage(__('No product selected!'));
                        }
                        break;

                    case "start_send":
                        if (!isset($data['links']['send_products'])) {
                            $this->messageManager->addErrorMessage(__('No product to send!'));
                        } else {
                            $send_products = $data['links']['send_products'];
                            $save_result = $this->saveTransferStockProduct($send_products, true, false);
                            if ($save_result) {
                                $this->messageManager->addSuccessMessage(__('sent ' . count($send_products) . ' product(s) to the destination location!'));
                                $transferstock->setData("status", TransferStockInterface::STATUS_PROCESSING);
                            }
                        }

                        break;

                    case "direct_transfer":
                        if (!isset($data['links']['send_products'])) {
                            $this->messageManager->addErrorMessage(__('No product to send!'));
                        } else {
                            $send_products = $data['links']['send_products'];
                            $save_result = $this->saveTransferStockProduct($send_products, false, true);
                            if ($save_result) {
                                $this->messageManager->addSuccessMessage(__('Sent ' . count($send_products) . ' product(s) directly to the destination location!'));
                                $transferstock->setData("status", TransferStockInterface::STATUS_COMPLETED);
                            }
                        }


                        break;

                    case "save_receiving":
                        if (isset($data['links']['receiving_products']) || isset($data['links']['shortfall_products'])) {
                            $receiving_products = isset($data['links']['receiving_products']) ? $data['links']['receiving_products'] : $data['links']['shortfall_products'];
                            $receiving_products = $this->prepareProductData($receiving_products, TransferActivityModel::ACTIVITY_TYPE_RECEIVING);
                            if (sizeof($receiving_products) == 0) {
                                $this->messageManager->addErrorMessage(__('Can not receving more!'));
                            } else {
                                $this->saveTransferActivityProduct($receiving_products, TransferActivityModel::ACTIVITY_TYPE_RECEIVING);
                                $this->messageManager->addSuccessMessage(__('Created a receiving successfully!'));
                                $emailNotification = $this->_emailNotificationFactory->create();
                                $emailNotification->notifyCreateDelivery($id);
                            }

                        } else {
                            $this->messageManager->addErrorMessage(__('No product to receive!'));
                        }
                        break;

                    case 'save_return':
                        if (isset($data['links']['return_products']) || isset($data['links']['shortfall_products'])) {
                            $return_products = isset($data['links']['return_products']) ? $data['links']['return_products'] : $data['links']['shortfall_products'];
                            $return_products = $this->prepareProductData($return_products, TransferActivityModel::ACTIVITY_TYPE_RETURN);
                            if (sizeof($return_products) == 0) {
                                $this->messageManager->addErrorMessage(__('Can not create Return stock!'));
                            } else {
                                $this->saveTransferActivityProduct($return_products, TransferActivityModel::ACTIVITY_TYPE_RETURN);
                                $this->messageManager->addSuccessMessage(__('Return successfully!'));
                                $emailNotification = $this->_emailNotificationFactory->create();
                                $emailNotification->notifyReturn($id);
                            }
                        } else {
                            $this->messageManager->addErrorMessage(__('No product to return !'));
                        }
                        break;

                    case 'save_return_convert_to_send_stock':
                        if (isset($data['links']['return_products'])) {
                            $return_products = $data['links']['return_products'];
                            $return_products = $this->prepareProductData($return_products, TransferActivityModel::ACTIVITY_TYPE_RETURN);
                            if (sizeof($return_products) == 0) {
                                $this->messageManager->addErrorMessage(__('Can not create Return stock!'));
                            } else {
                                $dataSession = $this->_objectManager->get('\Magento\Newsletter\Model\Session');
                                $dataSession->setReturnProductData($return_products);
                                return $resultRedirect->setPath('*/transferstock/returnStock/', ['id' => $id, 'type' => TransferStockModel::TYPE_SEND, '_current' => true]);
                                break;
                            }
                        } else {
                            $this->messageManager->addErrorMessage(__('Please select products to return! '));
                        }
                        break;

                    case "complete":
                        $transferstock->setData("status", TransferStockInterface::STATUS_COMPLETED);
                        $this->messageManager->addSuccessMessage(__('Marked send stock #' . $transferstock->getTransferstockCode() . ' as completed'));
                        break;

                }
            } else {
                //update transfer stock information
                $data = $this->prepareTransferStockData($data);
                $transferstock->setData($data);
            }
            //save transfer stock information
            try {
                $transferstock->save();

                $this->_coreRegistry->unregister("current_send_stock");

                return $resultRedirect->setPath('*/*/edit', ['id' => $transferstock->getTransferstockId(), '_current' => true]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the page.'));
            }
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param $receiving_products
     * @param $type
     * @return mixed
     */
    public function prepareProductData($receiving_products, $type)
    {
        if ($type == TransferActivityModel::ACTIVITY_TYPE_RECEIVING) {
            foreach ($receiving_products as $i => &$value) {
                if ($value['qty_sent'] < ($value['qty_received'] + $value['qty_returned'] + $value['qty'])) {
                    $value['qty'] = max($value['qty_sent'] - $value['qty_received'] - $value['qty_returned'], 0);
                    if ($value['qty'] == 0) {
                        unset($receiving_products[$i]);
                    }
                }
            }
            return $receiving_products;
        }
        if ($type == TransferActivityModel::ACTIVITY_TYPE_RETURN) {
            foreach ($receiving_products as $i => &$value) {
                if ($value['qty_sent'] < ($value['qty_received'] + $value['qty_returned'] + $value['qty'])) {
                    $value['qty'] = max($value['qty_sent'] - $value['qty_received'] - $value['qty_returned'], 0);
                    if ($value['qty'] == 0) {
                        unset($receiving_products[$i]);
                    }
                }
            }
            return $receiving_products;
        }
    }

    /**
     * Create new transfer stock with given data
     * @param $data
     *
     */
    public function prepareTransferStockData($data)
    {

        $adminUser = $this->adminSession->getUser();
        if ($adminUser->getId()) {
            $adminName = $adminUser->getUserName();
        } else {
            $adminName = '';
        }

        if (isset($data['source_warehouse_id'])) {
            $data['source_warehouse_code'] = $this->getWarehouseCode($data['source_warehouse_id']);
        }

        if (isset($data['des_warehouse_id'])) {
            $data['des_warehouse_code'] = $this->getWarehouseCode($data['des_warehouse_id']);
        }

        $data['type'] = TransferStockModel::TYPE_SEND;
        $data['created_by'] = $adminName;
        $data['created_at'] = strftime('%Y-%m-%d %H:%M:%S', $this->timezone->scopeTimeStamp());
        return $data;
    }

    /**
     * @param $warehouseId
     * @return string
     */
    public function getWarehouseCode($warehouseId)
    {
        /** @var \Magestore\InventorySuccess\Model\Warehouse $warehouse */
        $warehouse = $this->_warehouseFactory->create()->load($warehouseId);
        return $warehouse->getWarehouseCode();
    }

    /**
     * create new delivery
     */
    public function createTransferActivity($activity_products, $activity_type)
    {

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
     * count total qty of products in a delivery
     * @param $activity_products
     * @return int
     */
    public function getTransferActivityTotalQty($activity_products)
    {
        $total_qty = 0;
        foreach ($activity_products as $product) {
            $total_qty = $total_qty + $product['qty'];
        }
        return $total_qty;
    }

    /**
     * save deliery product.
     * Steps:
     * + create new deliery
     * + reformat post data of delivery_products
     * + save delivery product into model TransferActivityProduct
     * @param $delivery_products
     */
    public function saveTransferActivityProduct($activity_products, $activity_type)
    {

        $transferActivity = $this->createTransferActivity($activity_products, $activity_type);
        $activityId = $transferActivity->getActivityId();
        if ($activityId) {
            //reformat delivery product data
            $products = [];
            foreach ($activity_products as $item) {
                $product = [];
                $product['activity_id'] = $activityId;
                $product['product_id'] = $item['id'];
                $product['product_name'] = $item['name'];
                $product['product_sku'] = $item['sku'];
                $product['qty'] = $item['qty'];
                $products[$item['id']] = $product;
            }

            /** @var \Magestore\InventorySuccess\Model\TransferStock\TransferActivityManagement $transferActivityManagement */
            $transferActivityManagement = $this->_objectManager->create('\Magestore\InventorySuccess\Model\TransferStock\TransferActivityManagement');
            $transferActivityManagement->setProducts($transferActivity, $products);
            $transferActivityManagement->updateStock($transferActivity);
            $transferstockId = $this->getRequest()->getParam('id');
            $transferActivityManagement->updateTransferstockProductQtySummary($transferstockId, $activity_products, $activity_type);
        }
    }

    /**
     * save send stock products
     * if admin click on "start request stock" ($update_stock=true) then process update stock
     * @param $data
     * @param $update_stock
     */
    public function saveTransferStockProduct($send_products, $updateStock, $directTransfer)
    {
        if (!$this->validateStockDelivery($send_products)) {
            $this->_coreRegistry->register("send_products", $send_products);
            return false;
        }
        /** @var \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement $transferStockManagement */
        $transferStockManagement = $this->_objectManager->create('\Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement');

        $data = $this->reformatPostData($send_products);
        $id = $this->getRequest()->getParam('id');
        $transferStock = $this->_objectManager->create('\Magestore\InventorySuccess\Model\TransferStock');
        $transferStock->load($id);

        $transferStockManagement->saveTransferStockProduct($id, $data);
        if ($directTransfer) {
            $transferStockManagement->updateStock($transferStock);
            $this->saveTransferActivityProduct($send_products, TransferActivityInterface::ACTIVITY_TYPE_RECEIVING);
            $emailNotification = $this->_emailNotificationFactory->create();
            $emailNotification->notifyCreateDirectTransfer($id);
            return true;
        } elseif ($updateStock) {
            $transferStockManagement->updateStock($transferStock);
            $emailNotification = $this->_emailNotificationFactory->create();
            $emailNotification->notifyCreateNewTransfer($id);
            return true;
        }
        return true;
    }

    public function reformatPostData($data)
    {
        $id = $this->getRequest()->getParam('id');
        $newData = [];

        foreach ($data as $index => $value) {
            if (!isset($value['id']) || !$value['id'] || !isset($value['qty'])) {
                continue;
            }
            $item = [];
            $item['transferstock_id'] = $id;
            $item['product_id'] = $value['id'];
            $item['product_name'] = $value['name'];
            $item['product_sku'] = $value['sku'];
            $item['qty'] = $value['qty'];

            $newData[$value['id']] = $item;
        }
        return $newData;
    }

    /** validate send stock qty
     * @param $send_products
     * @return array|bool
     */
    public function validateStockDelivery($send_products)
    {
        /** @var \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement $transferStockManagement */
        $transferStockManagement = $this->_objectManager->create('\Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement');
        $id = $this->getRequest()->getParam('id');
        $transferStock = $this->_objectManager->create('\Magestore\InventorySuccess\Model\TransferStock');
        $transferStock->load($id);
        $warehouseId = $transferStock->getSourceWarehouseId();
        $productStocks = [];
        foreach ($send_products as $item) {
            if (!isset($item['id']) || !$item['id'] || !isset($item['qty'])) {
                continue;
            }
            $productStocks[$item['id']] = $item['qty'];
        }
        $result = $transferStockManagement->validateStockDelivery($productStocks, $warehouseId);
        if (!$result) {
            $this->messageManager->addErrorMessage(__('Send qty must be less than available qty!'));
            return false;
        }
        return true;
    }

}
