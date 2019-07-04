<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\External;

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
        $this->_emailNotificationFactory = $emailNotificationFactory;
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = $this->getRequest()->getParam('id');
        $data = $this->getRequest()->getPostValue();
        $action = $this->getRequest()->getParam('action');

        switch ($action) {
            case 'download_summary':
                return $resultRedirect->setPath('*/*/downloadsummary/id/' . $id);
                break;
        }

        if (!empty($data)) {
            if (isset($data['links']) && is_string($data['links'])) {
                $data['links'] = json_decode($data['links'], true);
            }
            $this->_coreRegistry->register("current_external_transfer_stock", $data);
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
            return $resultRedirect->setPath('*/*/edit', ['id' => $id, '_current' => true]);
        }


        if ($data) {

            $transferstock = $this->_transferStockFactory->create();
            if ($id) {
                $transferstock->load($id);

                switch ($action) {
                    case "send_email":
                        return $resultRedirect->setPath('*/*/sendEmail',
                            [
                                'id' => $id,
                                '_current' => true,
                                'type' => $transferstock->getType()
                            ]
                        );
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
                        $type = $transferstock->getType();
                        $transferstock->delete();
                        $this->messageManager->addSuccessMessage(__('The request #' . $transferstock->getTransferstockCode() . ' has been deleted !'));
                        if ($type == TransferStockInterface::TYPE_FROM_EXTERNAL) {
                            return $resultRedirect->setPath('*/transferstock_history/fromexternal');
                        } else {
                            return $resultRedirect->setPath('*/transferstock_history/toexternal');
                        }
                        break;
                    case "save_general":
                        $transferstock->setData("reason", $data['reason']);
                        $transferstock->setData("notifier_emails", $data['notifier_emails']);
                        $transferstock->setData("transferstock_code", $data['transferstock_code']);
                        $this->messageManager->addSuccessMessage(__('Saved general information successfully!'));
                        break;

                    case "prepare_product":
                        $transferstock->setData("status", TransferStockInterface::STATUS_PROCESSING);
                        $this->messageManager->addSuccessMessage(__('You are now ready to prepare a product list for transfer stock'));
                        break;

                    case "save_product":
                        $external_products = [];
                        if (isset($data['links']['external_products']) && !empty($data['links']['external_products'])) {
                            $external_products = $data['links']['external_products'];
                        }
                        $isValid = $this->validateStock($external_products);

                        if (!$isValid) {
                            $this->_coreRegistry->register("external_products", $external_products);
                            $this->messageManager->addErrorMessage(__('Transfer qty must be less than available qty!'));
                        } else {
                            $this->saveTransferStockProduct($external_products, false);
                            $this->messageManager->addSuccessMessage(__('Saved ' . count($external_products) . ' product(s) to the transfer list!'));
                        }
                        break;

                    case "direct_transfer":
                        if (!isset($data['links']['external_products'])) {
                            $this->messageManager->addErrorMessage(__('No product to transfer!'));
                        } else {

                            $external_products = $data['links']['external_products'];
                            $isValid = $this->validateStock($external_products);

                            if (!$isValid) {
                                $this->_coreRegistry->register("external_products", $external_products);
                                $this->messageManager->addErrorMessage(__('Transfer qty must be less than available qty!'));
                            } else {
                                $transferstock->setData("status", TransferStockInterface::STATUS_COMPLETED);
                                $this->saveTransferStockProduct($external_products, true);
                                $this->messageManager->addSuccessMessage(__('Transfered  ' . count($external_products) . ' product(s) successfully!'));
                                $emailNotification = $this->_emailNotificationFactory->create();
                                $emailNotification->notifyCreateNewTransfer($id);
                            }
                        }
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
                return $resultRedirect->setPath('*/*/edit', [
                        'id' => $transferstock->getTransferstockId(),
                        '_current' => true,
                        'type' => $transferstock->getType()
                    ]

                );
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the page.'));
            }
        }
        return $resultRedirect->setPath('*/*/');
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

        $type = TransferStockModel::TYPE_TO_EXTERNAL;
        if ($this->getRequest()->getParam('type')) {
            $type = $this->getRequest()->getParam('type');
        }

        switch ($type) {
            case TransferStockInterface::TYPE_TO_EXTERNAL:
                $data['source_warehouse_code'] = $this->getWarehouseCode($data['source_warehouse_id']);
                break;
            case TransferStockInterface::TYPE_FROM_EXTERNAL:
                $data['des_warehouse_code'] = $this->getWarehouseCode($data['des_warehouse_id']);
                break;
        }

        $data['type'] = $type;
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
     * save send stock products
     * if admin click on "start request stock" ($update_stock=true) then process update stock
     * @param $data
     * @param $update_stock
     */
    public function saveTransferStockProduct($data, $update_stock)
    {

        /** @var \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement $transferStockManagement */
        $transferStockManagement = $this->_objectManager->create('\Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement');

        $data = $this->reformatPostData($data);
        $id = $this->getRequest()->getParam('id');
        $transferStock = $this->_objectManager->create('\Magestore\InventorySuccess\Model\TransferStock');
        $transferStock->load($id);

        $transferStockManagement->saveTransferStockProduct($id, $data);
        if ($update_stock) {
            $transferStockManagement->updateStock($transferStock);
        }

    }

    public function reformatPostData($data)
    {
        $id = $this->getRequest()->getParam('id');
        $newData = [];

        foreach ($data as $index => $value) {
            $item = [];
            $item['transferstock_id'] = $id;
            $item['product_id'] = $value['id'];
            $item['product_name'] = $value['name'];
            $item['product_sku'] = $value['sku'];
            if (isset($value['qty']) && is_numeric($value['qty'])) {
                $item['qty'] = $value['qty'];
            } else {
                $item['qty'] = 0;
            }
            $newData[$value['id']] = $item;
        }
        return $newData;
    }

    /** validate send stock qty
     * @param $send_products
     * @return array|bool
     */
    public function validateStock($send_products)
    {

        /** @var \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement $transferStockManagement */
        $transferStockManagement = $this->_objectManager->create('\Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement');
        $id = $this->getRequest()->getParam('id');
        $transferStock = $this->_objectManager->create('\Magestore\InventorySuccess\Model\TransferStock');
        $transferStock->load($id);

        if ($transferStock->getType() == TransferStockModel::TYPE_TO_EXTERNAL) {

            $warehouseId = $transferStock->getSourceWarehouseId();
            $productStocks = [];
            foreach ($send_products as $item) {
                $productStocks[$item['id']] = $item['qty'];
            }
            return $transferStockManagement->validateStockDelivery($productStocks, $warehouseId);
        }

        return true;
    }

}
