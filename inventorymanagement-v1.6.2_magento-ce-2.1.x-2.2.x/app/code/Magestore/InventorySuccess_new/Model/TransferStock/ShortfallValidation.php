<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\TransferStock;

use Magestore\InventorySuccess\Model\TransferStock;

class ShortfallValidation
{
    /** @var  \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\CollectionFactory */
    protected $_transferStockProductFactory;

    /** @var  \Magestore\InventorySuccess\Model\TransferStockFactory */
    protected $_transferStockFactory;

    protected $_messageManager;

    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct\CollectionFactory $transferStockProduct,
        \Magestore\InventorySuccess\Model\TransferStockFactory $transferStockFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->_transferStockProductFactory = $transferStockProduct;
        $this->_transferStockFactory = $transferStockFactory;
        $this->_messageManager = $messageManager;
    }

    /**
     * Add notice
     * @return $this
     */
    public function _showNoticeShortfall($transferstock_id,$type)
    {
        if($this->_checkShortfallList($transferstock_id,$type) && $this->_checkShortFallStatus($transferstock_id)) {
            $this->_messageManager->addNotice(
                __('Some stock are in shortfall list ! Please continue to receive or return them before COMPLETE this transaction.')
            );
        }
        return $this;
    }

    /**
     * @param $transferstock_id
     * @return bool
     */
    protected function _checkShortfallList($transferstock_id,$type){
        /* for type = request */
        if($type == TransferStock::TYPE_REQUEST) {
            $transferStockProduct = $this->_transferStockProductFactory->create();
            $transferStockProduct->addFieldToFilter('transferstock_id', $transferstock_id);
            $transferStockProduct->getSelect()->where('(qty - qty_delivered) > ? OR (qty_delivered - qty_received - qty_returned) > ? ', 0, 0);
            if ($transferStockProduct->getSize() > 0) {
                return true;
            }
            return false;
        }
        /* for type = send */
        if($type == TransferStock::TYPE_SEND) {
            $transferStockProduct = $this->_transferStockProductFactory->create();
            $transferStockProduct->addFieldToFilter('transferstock_id', $transferstock_id);
            $transferStockProduct->getSelect()->where('(qty - qty_received - qty_returned) > ? ', 0);
            if ($transferStockProduct->getSize() > 0) {
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param $transferstock_id
     * @return bool
     */
    public function _checkShortFallStatus($transferstock_id){
        $transferStockManagement = $this->_transferStockFactory->create()->load($transferstock_id);
        if($transferStockManagement->getStatus() == TransferStock::STATUS_PROCESSING){
            return true;
        }
        return false;
    }
}
