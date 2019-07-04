<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model;

use Magestore\InventorySuccess\Api\InstallManagementInterface;

class InstallManagement implements InstallManagementInterface
{
    /**
     *
     * @var \Magestore\InventorySuccess\Model\ResourceModel\InstallManagement
     */
    protected $_resource;

    /**
     *
     * @var \Magento\CatalogInventory\Model\Indexer\Stock
     */
    protected $stockIndexer;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * @var \Magestore\InventorySuccess\Api\Logger\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\InstallManagement $installManagementResource,
        \Magento\CatalogInventory\Model\Indexer\Stock $stockIndexer,
        \Magento\Framework\App\State $appState,
        \Magestore\InventorySuccess\Api\Logger\LoggerInterface $logger
    )
    {
        $this->_resource = $installManagementResource;
        $this->stockIndexer = $stockIndexer;
        $this->appState = $appState;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function calculateQtyToShip()
    {
        $this->getResource()->calculateQtyToShip();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function transferProductsToDefaultWarehouse()
    {
        $this->getResource()->transferProductsToDefaultWarehouse();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function createDefaultWarehouse()
    {
        $this->getResource()->createDefaultWarehouse();
        return $this;
    }

    /**
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\InstallManagement
     */
    public function getResource()
    {
        return $this->_resource;
    }

    /**
     * create default notification rule
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\InstallManagement
     */
    public function createDefaultNotificationRule()
    {
        /** @var \Magestore\InventorySuccess\Api\LowStockNotification\RuleManagementInterface $ruleManagement */
        $ruleManagement = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Api\LowStockNotification\RuleManagementInterface'
        );
        $ruleManagement->createDefaultNotificationRule();
    }

    /**
     * @inheritdoc
     */
    public function transferWarehouseProductToMagentoStockItem()
    {
        $this->getResource()->transferWarehouseProductToMagentoStockItem();
    }

    /**
     * convert data from os_warehouse_order_item into sales_order_item
     * convert data from os_warehouse_shipment_item into sales_shipment_item
     * convert data from os_warehouse_creditmemo_item into sales_creditmemo_item
     *
     * @return \Magestore\InventorySuccess\Model\InstallManagement
     */
    public function convertSaleItemsData()
    {
        $this->getResource()->convertSaleItemsData();
        return $this;
    }

    /**
     * Update stock id value base on website id in table cataloginventory_stock_item
     * Magento 2 update unique fields of table cataloginventory_stock_item
     * from 'product_id' and 'website_id' to 'product_id' and 'stock_id'
     *
     * @return \Magestore\InventorySuccess\Model\InstallManagement
     */
    public function updateStockId()
    {
        $this->getResource()->updateStockId();

        /** Reindex stock item and stock status */
        try{
            $this->appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
        } catch(\Exception $e) {
            $this->appState->getAreaCode();
        }        
        
        try {
            $this->stockIndexer->executeFull();
        } catch (\Exception $e) {
            $this->logger->log($e->getMessage(), 'UpdateStockId');
        }
        return $this;
    }
}