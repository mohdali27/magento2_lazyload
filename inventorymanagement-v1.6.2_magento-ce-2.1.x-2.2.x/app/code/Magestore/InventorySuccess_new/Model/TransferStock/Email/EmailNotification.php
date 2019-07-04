<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Created by PhpStorm.
 * User: steve
 * Date: 07/09/2016
 * Time: 23:07
 */

namespace Magestore\InventorySuccess\Model\TransferStock\Email;

use Magestore\InventorySuccess\Api\Data\TransferStock\Email as TransferEmailNotifyData;
use Magestore\InventorySuccess\Api\Data\TransferStock\TransferStockInterface;

class EmailNotification extends \Magestore\InventorySuccess\Model\Email\EmailManagement
{

    /** @var  \Magestore\InventorySuccess\Model\TransferStockFactory */
    protected $_transferStockFactory;

    /** @var   */
    protected $_urlBuilder;

    public function __construct(
        \Magestore\InventorySuccess\Model\TransferStockFactory $transferStockFactory,
        \Magento\Backend\Model\UrlInterface $urlBuilder
    )
    {
        $this->_transferStockFactory = $transferStockFactory;
        $this->_urlBuilder = $urlBuilder;
    }

    /**
     * send email to notifierEmails when a transfer stock is created.
     * @param $transferstockId
     */
    public function notifyCreateNewTransfer($transferstockId){

        $transferStock = $this->_transferStockFactory->create()->load($transferstockId);
        $this->setReceivers($transferStock->getNotifierEmails());
        $this->setEmailTemplate(TransferEmailNotifyData::EMAIL_TEMPLATE_TRANSFERSTOCK_CREATE);

        $templateVars = [];
        $templateVars['transferstock_id'] = $transferstockId;
        $templateVars['transferstock_code'] = $transferStock->getTransferstockCode();
        $templateVars['total_items'] = $transferStock->getQty();
        $templateVars['created_by'] = $transferStock->getCreatedBy();

        switch ($transferStock->getType()){
            case TransferStockInterface::TYPE_REQUEST:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_request/edit/id/" . $transferstockId);
                break;
            case TransferStockInterface::TYPE_SEND:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_send/edit/id/" . $transferstockId);
                break;
            case TransferStockInterface::TYPE_TO_EXTERNAL:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_external/edit/type/to_external/id/" . $transferstockId);
                break;
            case TransferStockInterface::TYPE_FROM_EXTERNAL:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_external/edit/type/from_external/id/" . $transferstockId);
                break;
        }

        $this->setTemplateVars($templateVars);

        //send email
        $this->sendEmail();
    }


    /**
     * send email to notifierEmails when a transfer stock is created.
     * @param $transferstockId
     */
    public function notifyReturn($transferstockId){

        $transferStock = $this->_transferStockFactory->create()->load($transferstockId);
        $this->setReceivers($transferStock->getNotifierEmails());
        $this->setEmailTemplate(TransferEmailNotifyData::EMAIL_TEMPLATE_TRANSFERSTOCK_RETURN);

        $templateVars = [];
        $templateVars['transferstock_id'] = $transferstockId;
        $templateVars['transferstock_code'] = $transferStock->getTransferstockCode();
        $templateVars['total_items'] = $transferStock->getQty();
        $templateVars['created_by'] = $transferStock->getCreatedBy();

        switch ($transferStock->getType()){
            case TransferStockInterface::TYPE_REQUEST:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_request/edit/id/" . $transferstockId);
                break;
            case TransferStockInterface::TYPE_SEND:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_send/edit/id/" . $transferstockId);
                break;
            case TransferStockInterface::TYPE_TO_EXTERNAL:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_external/type/to_external/edit/id/" . $transferstockId);
                break;
            case TransferStockInterface::TYPE_FROM_EXTERNAL:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_external/type/from_external/edit/id/" . $transferstockId);
                break;
        }
        $this->setTemplateVars($templateVars);
        //send email
        $this->sendEmail();
    }

    /**
     * send email to notifierEmails when a transfer stock is created.
     * @param $transferstockId
     */
    public function notifyCreateNewTransferOmniChannel($transferstockId,$whId){

        $transferStock = $this->_transferStockFactory->create()->load($transferstockId);
        //$this->setReceivers($transferStock->getNotifierEmails());
        $email = $this->getWarehouseEmailInformation($whId).','.$transferStock->getNotifierEmails();
        if(!$email){
            return;
        }
        $this->setReceivers($email);
        $this->setEmailTemplate(TransferEmailNotifyData::EMAIL_TEMPLATE_TRANSFERSTOCK_CREATE_ATTACH_FILE);

        $templateVars = [];
        $templateVars['transferstock_id'] = $transferstockId;
        $templateVars['transferstock_code'] = $transferStock->getTransferstockCode();
        $templateVars['total_items'] = $transferStock->getQty();
        $templateVars['created_by'] = $transferStock->getCreatedBy();

        switch ($transferStock->getType()){
            case TransferStockInterface::TYPE_REQUEST:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_request/edit/id/" . $transferstockId);
                $templateVars['transfer_link_download'] = $this->getDownloadUrl($transferstockId);
                break;
            case TransferStockInterface::TYPE_SEND:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_send/edit/id/" . $transferstockId);
                $templateVars['transfer_link_download'] = $this->getDownloadUrl($transferstockId);
                break;
            case TransferStockInterface::TYPE_TO_EXTERNAL:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_external/type/to_external/edit/id/" . $transferstockId);
                $templateVars['transfer_link_download'] = $this->getDownloadUrl($transferstockId);
                break;
            case TransferStockInterface::TYPE_FROM_EXTERNAL:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_external/type/from_external/edit/id/" . $transferstockId);
                $templateVars['transfer_link_download'] = $this->getDownloadUrl($transferstockId);
                break;
        }
        $this->setTemplateVars($templateVars);
        //send email
        $this->sendEmail();
    }

    /**
     * @param $warehouseIds
     * @return bool|null|string
     */
    public function getWarehouseEmailInformation($warehouseIds) {
        if(!$warehouseIds){
            return;
        }
        /** @var  \Magestore\InventorySuccess\Model\Warehouse $warehouseModel */
        $warehouseModel = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\WarehouseFactory'
        )->create();
        $warehouseModel->load($warehouseIds);
        if ($warehouseModel->getId()) {
            return $warehouseModel->getContactEmail();
        }
        return false;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getDownloadUrl($id)
    {
        /** @var \Magento\Framework\Url $urlBuilder */
        $urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Framework\Url'
        );
        return $urlBuilder->getUrl('inventorysuccess/transferStock/download', ['id' =>$id]);
    }

    /**
     * send email to notifierEmails when a transfer stock is created.
     * @param $transferstockId
     */
    public function notifyCreateDelivery($transferstockId){

        $transferStock = $this->_transferStockFactory->create()->load($transferstockId);
        $this->setReceivers($transferStock->getNotifierEmails());
        $this->setEmailTemplate(TransferEmailNotifyData::EMAIL_TEMPLATE_TRANSFERSTOCK_DELIVERY);

        $templateVars = [];
        $templateVars['transferstock_id'] = $transferstockId;
        $templateVars['transferstock_code'] = $transferStock->getTransferstockCode();
        $templateVars['total_items'] = $transferStock->getQty();
        $templateVars['created_by'] = $transferStock->getCreatedBy();

        switch ($transferStock->getType()){
            case TransferStockInterface::TYPE_REQUEST:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_request/edit/id/" . $transferstockId);
                break;
            case TransferStockInterface::TYPE_SEND:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_send/edit/id/" . $transferstockId);
                break;
            case TransferStockInterface::TYPE_TO_EXTERNAL:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_external/type/to_external/edit/id/" . $transferstockId);
                break;
            case TransferStockInterface::TYPE_FROM_EXTERNAL:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_external/type/from_external/edit/id/" . $transferstockId);
                break;
        }

        $this->setTemplateVars($templateVars);

        //send email
        $this->sendEmail();
    }

    /**
     * send email to notifierEmails when a transfer stock is created.
     * @param $transferstockId
     */
    public function notifyCreateReceiving($transferstockId){

        $transferStock = $this->_transferStockFactory->create()->load($transferstockId);
        $this->setReceivers($transferStock->getNotifierEmails());
        $this->setEmailTemplate(TransferEmailNotifyData::EMAIL_TEMPLATE_TRANSFERSTOCK_RECEIVING);

        $templateVars = [];
        $templateVars['transferstock_id'] = $transferstockId;
        $templateVars['transferstock_code'] = $transferStock->getTransferstockCode();
        $templateVars['total_items'] = $transferStock->getQty();
        $templateVars['created_by'] = $transferStock->getCreatedBy();

        switch ($transferStock->getType()){
            case TransferStockInterface::TYPE_REQUEST:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_request/edit/id/" . $transferstockId);
                break;
            case TransferStockInterface::TYPE_SEND:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_send/edit/id/" . $transferstockId);
                break;
            case TransferStockInterface::TYPE_TO_EXTERNAL:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_external/type/to_external/edit/id/" . $transferstockId);
                break;
            case TransferStockInterface::TYPE_FROM_EXTERNAL:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_external/type/from_external/edit/id/" . $transferstockId);
                break;
        }

        $this->setTemplateVars($templateVars);

        //send email
        $this->sendEmail();
    }


    /**
     * @param $transferstockId
     */
    public function notifyCreateDirectTransfer($transferstockId){

        $transferStock = $this->_transferStockFactory->create()->load($transferstockId);
        $this->setReceivers($transferStock->getNotifierEmails());
        $this->setEmailTemplate(TransferEmailNotifyData::EMAIL_TEMPLATE_TRANSFERSTOCK_DIRECT_TRANSFER);

        $templateVars = [];
        $templateVars['transferstock_id'] = $transferstockId;
        $templateVars['transferstock_code'] = $transferStock->getTransferstockCode();
        $templateVars['total_items'] = $transferStock->getQty();
        $templateVars['created_by'] = $transferStock->getCreatedBy();

        switch ($transferStock->getType()){
            case TransferStockInterface::TYPE_REQUEST:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_request/edit/id/" . $transferstockId);
                break;
            case TransferStockInterface::TYPE_SEND:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_send/edit/id/" . $transferstockId);
                break;
            case TransferStockInterface::TYPE_TO_EXTERNAL:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_external/type/to_external/edit/id/" . $transferstockId);
                break;
            case TransferStockInterface::TYPE_FROM_EXTERNAL:
                $templateVars['transfer_link'] = $this->_urlBuilder->getUrl("inventorysuccess/transferstock_external/type/from_external/edit/id/" . $transferstockId);
                break;
        }

        $this->setTemplateVars($templateVars);

        //send email
        $this->sendEmail();
    }


}