<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct;

use Magestore\InventorySuccess\Model\ResourceModel\AbstractCollection;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product as WarehouseProductResource;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;


class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'transferstock_product_id';


    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\InventorySuccess\Model\TransferStock\TransferStockProduct', 'Magestore\InventorySuccess\Model\ResourceModel\TransferStock\TransferStockProduct');
    }

    /**
     * Get Transfer stock product collection by transfer stock id and warehouse id
     * 
     * @param null $transferstock_id
     * @param null $warehouse_id
     * @return $this
     */
    public function getTransferStockProduct($transferstock_id = null, $warehouse_id = null)
    {
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

        $this->getSelect()->columns('main_table.qty as qty_requested');
        if ($transferstock_id) {
            $this->getSelect()->where("main_table.transferstock_id=$transferstock_id");
        }

        if ($warehouse_id) {
            $this->getSelect()->joinLeft(
                ['warehouse_product' => $this->getTable(WarehouseProductResource::MAIN_TABLE)],
                'main_table.product_id = warehouse_product.product_id',
                [
                    'total_qty' => 'total_qty',
                    'available_qty' => 'qty'
                ]
            );
            $this->getSelect()->where("warehouse_product." . WarehouseProductInterface::WAREHOUSE_ID . "=$warehouse_id");
        }

        return $this;
    }


}
