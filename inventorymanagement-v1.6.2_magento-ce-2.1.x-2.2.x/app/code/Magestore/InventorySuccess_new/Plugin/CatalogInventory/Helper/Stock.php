<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Plugin\CatalogInventory\Helper;

use Magestore\InventorySuccess\Helper\Data as InventoryHelper;
use Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Stock {

    /**
     *
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product
     */
    protected $warehouseStockResource;

    /**
     * @var InventoryHelper
     */
    protected $inventoryHelper;

    /**
     * @var \Magestore\InventorySuccess\Helper\System
     */
    protected $systemHelper;

    /**
     * @var WarehouseManagementInterface
     */
    protected $warehouseManagement;

    /**
     * Core store config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;


    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product $warehouseStockResource,
        InventoryHelper $inventoryHelper,
        \Magestore\InventorySuccess\Helper\System $systemHelper,
        WarehouseManagementInterface $warehouseManagement,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $coreRegistry
    )
    {
        $this->warehouseStockResource = $warehouseStockResource;
        $this->inventoryHelper = $inventoryHelper;
        $this->systemHelper = $systemHelper;
        $this->warehouseManagement = $warehouseManagement;
        $this->scopeConfig = $scopeConfig;
        $this->coreRegistry = $coreRegistry;
    }

    public function beforeAddIsInStockFilterToCollection(\Magento\CatalogInventory\Helper\Stock $stockHelper, \Magento\Catalog\Model\ResourceModel\Product\Collection $collection)
    {
        if(!$this->warehouseManagement->isGetStockFromWarehouse() && !$this->coreRegistry->registry('webpos_get_product_list')) {
            return [$collection];
        }

        $isShowOutOfStock = $this->scopeConfig->getValue(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $stockFlag = 'has_stock_status_filter';
        if(!$collection->getFlag($stockFlag)) {
            $this->warehouseStockResource->addStockDataToCollection($collection, !$isShowOutOfStock);
            $collection->setFlag($stockFlag, true);
        }
        return [$collection];
    }


}
