<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\TransferStock;


class GetBarcodeJson extends \Magestore\InventorySuccess\Controller\Adminhtml\TransferStock\AbstractRequest
{
    /**
     * @var \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement
     */
    protected $transferStockManagement;

    /**
     * GetBarcodeJson constructor.
     * @param \Magestore\InventorySuccess\Controller\Adminhtml\Context $context
     * @param \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement $transferStockManagement
     */
    public function __construct(
        \Magestore\InventorySuccess\Controller\Adminhtml\Context $context,
        \Magestore\InventorySuccess\Model\TransferStock\TransferStockManagement $transferStockManagement
    )
    {
        parent::__construct($context);
        $this->transferStockManagement = $transferStockManagement;
    }

    public function execute()
    {
        $this->getResponse()->representJson(
            $this->transferStockManagement->getSelectBarcodeProductListJson(
                $this->_request->getParam('transferstock_id')
            )
        );
    }

}


