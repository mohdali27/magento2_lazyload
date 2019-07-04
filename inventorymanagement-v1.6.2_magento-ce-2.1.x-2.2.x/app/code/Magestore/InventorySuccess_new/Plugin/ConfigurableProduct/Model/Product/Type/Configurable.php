<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Plugin\ConfigurableProduct\Model\Product\Type;
use Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Configurable 
{
    /**
     *
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product
     */
    protected $warehouseStockResource;
    
    /**
     * @var WarehouseManagementInterface 
     */
    protected $warehouseManagement;    
    
    /**
     * @var ScopeConfigInterface 
     */
    protected $scopeConfig;

    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Configurable constructor.
     * @param \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product $warehouseStockResource
     * @param WarehouseManagementInterface $warehouseManagement
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product $warehouseStockResource,
        WarehouseManagementInterface $warehouseManagement,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $coreRegistry
    )
    {
        $this->warehouseStockResource = $warehouseStockResource;
        $this->warehouseManagement = $warehouseManagement;
        $this->scopeConfig = $scopeConfig;
        $this->coreRegistry = $coreRegistry;
    }    
    
    public function afterGetUsedProductCollection(\Magento\ConfigurableProduct\Model\Product\Type\Configurable $productType, \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection $collection)
    {
        if(!$this->warehouseManagement->isGetStockFromWarehouse() && !$this->coreRegistry->registry('webpos_get_product_list')) {
            return $collection;
        }
        $stockFlag = 'has_stock_status_filter';
        if(!$collection->getFlag($stockFlag)) {
            $isShowOutOfStock = $this->scopeConfig->getValue(
                \Magento\CatalogInventory\Model\Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            //$isShowOutOfStock = true;
            $this->warehouseStockResource->addStockDataToCollection($collection, !$isShowOutOfStock);
            $collection->setFlag($stockFlag, true);
        }
        return $collection;        
    }
}

