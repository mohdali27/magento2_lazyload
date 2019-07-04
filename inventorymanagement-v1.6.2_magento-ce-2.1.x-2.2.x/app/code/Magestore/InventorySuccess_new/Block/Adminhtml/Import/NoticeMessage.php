<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Import;
class NoticeMessage extends \Magento\Backend\Block\Template
{
    protected $backendSession;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context
    ) {
        $this->backendSession = $context->getBackendSession();
        parent::__construct($context);
    }


    public function getNumberSkuInvalid()
    {
        return $this->backendSession->getData('sku_invalid', true);
    }

    public function isHasError()
    {
        return $this->backendSession->getData('error_import', true);
    }

    public function getInvalidFileCsvUrl()
    {
        $type = $this->backendSession->getData('import_type', true);
        if ($type == \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_ADJUST_STOCK) {
            return $this->getUrl('inventorysuccess/adjuststock/downloadinvalidcsv');
        } elseif ($type == \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_STOCKTAKING) {
            return $this->getUrl('inventorysuccess/stocktaking/downloadinvalidcsv');
        } elseif ($type == \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_TRANSFER_STOCK_TO_REQUEST) {
            return $this->getUrl('inventorysuccess/transferstock_request/downloadinvalidcsv');
        } elseif ($type == \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_TRANSFER_STOCK_TO_TRANSFER_DELIVERY) {
            return $this->getUrl('inventorysuccess/transferstock_request/downloadinvalidtransfercsv',
                array('type' => \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_TRANSFER_STOCK_TO_TRANSFER_DELIVERY));
        } elseif ($type == \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_TRANSFER_STOCK_TO_TRANSFER_RECEIVING) {
            return $this->getUrl('inventorysuccess/transferstock_request/downloadinvalidtransfercsv',
                array('type' => \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_TRANSFER_STOCK_TO_TRANSFER_RECEIVING));
        } elseif ($type == \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_TRANSFER_STOCK_TO_TRANSFER_SEND) {
            return $this->getUrl('inventorysuccess/transferstock_send/downloadinvalidcsv',
                array('type' => \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_TRANSFER_STOCK_TO_TRANSFER_SEND));
        } elseif ($type == \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_TRANSFER_STOCK_TO_TRANSFER_EXTERNAL_FROM) {
            return $this->getUrl('inventorysuccess/transferstock_external/downloadinvalidcsv',
                array('type' => \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_TRANSFER_STOCK_TO_TRANSFER_EXTERNAL_FROM));
        } elseif ($type == \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_TRANSFER_STOCK_TO_TRANSFER_EXTERNAL_TO) {
            return $this->getUrl('inventorysuccess/transferstock_external/downloadinvalidcsv',
                array('type' => \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_TRANSFER_STOCK_TO_TRANSFER_EXTERNAL_TO));
        } else {
            return $this->getUrl('inventorysuccess/transferstock_send/downloadinvalidcsv',
                array('type' => \Magestore\InventorySuccess\Model\Source\Adminhtml\ImportType::TYPE_TRANSFER_STOCK_TO_TRANSFER_SEND_RECEIVING));
        }
    }
}