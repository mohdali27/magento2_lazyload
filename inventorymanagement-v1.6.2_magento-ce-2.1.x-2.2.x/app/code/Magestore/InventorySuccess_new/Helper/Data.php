<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Helper;

/**
 * Helper Data.
 * @category Magestore
 * @package  Magestore_InventorySuccess
 * @module   Inventorysuccess
 * @author   Magestore Developer
 */
    
/**
 * Class Data
 * @package Magestore\InventorySuccess\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const LINK_WAREHOUSE_STORE_CONFIG_PATH = 'inventorysuccess/stock_control/link_warehouse_store_view';

    const SHOW_THUMBNAIL_PRODUCT_ON_GRID = 'inventorysuccess/interface/show_thumbnail_product';

    /**
     *
     * @param string $path
     * @return string
     */
    public function getStoreConfig($path){
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * get adjust stock change
     *
     * @param
     * @return boolean
     */
    public function getAdjustStockChange(){
        return $this->getStoreConfig('inventorysuccess/stock_control/adjust_stock_change');
    }

    /**
     * get adjust stock change
     *
     * @param
     * @return boolean
     */
    public function getDuplicateStockData(){
        return $this->getStoreConfig('inventorysuccess/stock_control/duplicate_stock_data');
    }

    /**
     * get barcode module enable
     *
     * @param
     * @return boolean
     */
    public function getBarcodeModuleEnable(){
        return $this->_moduleManager->isEnabled('Magestore_BarcodeSuccess');
    }
    
    /**
     *
     * @param
     * @return boolean
     */
    public function getLinkWarehouseStoreConfig(){
        return $this->getStoreConfig(self::LINK_WAREHOUSE_STORE_CONFIG_PATH);
    }    

    public function getShowThumbnailProduct() {
        return (boolean)$this->getStoreConfig(self::SHOW_THUMBNAIL_PRODUCT_ON_GRID);
    }

}