<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    
    /**
     * @var \Magestore\InventorySuccess\Api\InstallManagementInterface
     */
    protected $installManagement;
    
    /**
     * @var \Magestore\InventorySuccess\Api\StockMovement\StockTransferServiceInterface 
     */
    protected $stockTransferService;

    
    public function __construct(
        \Magestore\InventorySuccess\Api\InstallManagementInterface $installManagement,
        \Magestore\InventorySuccess\Api\StockMovement\StockTransferServiceInterface $stockTransferService
    )
    {
        $this->installManagement = $installManagement;
        $this->stockTransferService = $stockTransferService;
    }
    
    /**
     * {@inheritdoc}
     */    
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->installManagement->transferWarehouseProductToMagentoStockItem();
        }
        
        if (version_compare($context->getVersion(), '1.2.2', '<')) {
            /**
             * convert data from os_warehouse_order_item into sales_order_item
             * convert data from os_warehouse_shipment_item into sales_shipment_item
             * convert data from os_warehouse_creditmemo_item into sales_creditmemo_item
             */
            $this->installManagement->convertSaleItemsData();
        }
        
        if (version_compare($context->getVersion(), '1.2.4', '<')) {
            /**
             * combine stock movement records to stock transfer
             */
            $this->stockTransferService->addAllStockMovement();
        }        
        
        if (version_compare($context->getVersion(), '1.3.0', '<')) {
            /**
             * Update stock id value for table cataloginventory_stock_item
             */
            $this->installManagement->updateStockId();
        }        
    }

}