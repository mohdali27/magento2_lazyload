<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Plugin\CatalogInventory\Model;

use Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

class StockManagement
{
    
    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;
    
    public function __construct(
        \Magento\Framework\Registry $registry,
        StockConfigurationInterface $stockConfiguration
    )
    {
        $this->stockConfiguration = $stockConfiguration;
        $this->registry = $registry;
    }
    
//    public function beforeRegisterProductsSale(\Magento\CatalogInventory\Model\StockManagement $stockManagement, $items, $websiteId = null)
//    {
//        $this->registry->register(WarehouseManagementInterface::BEFORE_SUBTRACT_SALES_QTY, true);
//        return [$items, $websiteId];
//    }
    
    public function afterRegisterProductsSale(\Magento\CatalogInventory\Model\StockManagement $stockManagement, $fullSaveItems)
    {
//        $this->registry->unregister(WarehouseManagementInterface::BEFORE_SUBTRACT_SALES_QTY);
        $scope = $this->stockConfiguration->getDefaultScopeId();
        /** @var \Magento\CatalogInventory\Api\Data\StockItemInterface $_item */
        foreach ($fullSaveItems as $key => $_item){
            if ($scope && $_item->getWebsiteId()!= $scope){
                unset($fullSaveItems[$key]);
            }
        }
        return $fullSaveItems;
    }
}