<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\Stocktaking\Product;

/**
 * Class Collection
 * @package Magestore\InventorySuccess\Model\ResourceModel\Stocktaking\Product
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('Magestore\InventorySuccess\Model\Stocktaking\Product', 'Magestore\InventorySuccess\Model\ResourceModel\Stocktaking\Product');
    }

    /**
     * get stocktaking products
     *
     * @return void
     */
    public function getStocktakingProducts($stocktakingId){
        // get image
        $storeManager = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Store\Model\StoreManagerInterface');
        $path = $storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        $path .= 'catalog/product';
        $edition = \Magento\Framework\App\ObjectManager::getInstance()
            ->get('Magento\Framework\App\ProductMetadataInterface')
            ->getEdition();
        $rowId = strtolower($edition) == 'enterprise' ? 'row_id' : 'entity_id';
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute */
        $eavAttribute = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magento\Eav\Model\ResourceModel\Entity\Attribute');
        $productImagesAttributeId = $eavAttribute->getIdByCode(\Magento\Catalog\Model\Product::ENTITY, 'image');
        $this->getSelect()->joinLeft(
            array('catalog_product_entity_varchar_img' => $this->getTable('catalog_product_entity_varchar')),
            "main_table.product_id = catalog_product_entity_varchar_img.$rowId && 
                catalog_product_entity_varchar_img.attribute_id = $productImagesAttributeId && 
                catalog_product_entity_varchar_img.store_id = 0",
            array('')
        )->columns(array(
            'image' => 'catalog_product_entity_varchar_img.value',
            'image_url' => 'CONCAT("'.$path.'", catalog_product_entity_varchar_img.value)'
        ));
        $collection = $this->addFieldToFilter('stocktaking_id', $stocktakingId)
                           ->setOrder('product_id', 'DESC');
        return $collection;
    }

    /**
     * get stocktaking different products
     *
     * @return void
     */
    public function getStocktakingDifferentProducts($stocktakingId){
        $collection = $this->addFieldToFilter('stocktaking_id', $stocktakingId);
        $collection->getSelect()->columns(array(
                         'different_qty' => 'ABS(main_table.old_qty - main_table.stocktaking_qty)'))
                    ->where('ABS(main_table.old_qty - main_table.stocktaking_qty) != 0');
        return $collection;
    }
}
