<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Webpos\Catalog\Product;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magestore\InventorySuccess\Api\Warehouse\Location\MappingManagementInterface;
use Magento\Catalog\Model\ResourceModel\Product\Link;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Bundle\Model\ResourceModel\Selection;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product as WarehouseProductResource;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;

class WebposCatalogProductGetlist implements ObserverInterface
{
    /**
     * @var MappingManagementInterface
     */
    protected $_mappingManagement;

    /**
     * @var Link
     */
    protected $_productLink;

    /**
     * @var Configurable
     */
    protected $_configurable;

    /**
     * @var Selection
     */
    protected $_selection;

    /**
     * WebposCatalogProductGetlist constructor.
     * @param MappingManagementInterface $mappingManagement
     * @param Link $productLink
     * @param Configurable $configurable
     * @param Selection $selection
     */
    public function __construct(
        MappingManagementInterface $mappingManagement,
        Link $productLink,
        Configurable $configurable,
        Selection $selection
    )
    {
        $this->_mappingManagement = $mappingManagement;
        $this->_productLink = $productLink;
        $this->_configurable = $configurable;
        $this->_selection = $selection;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $collection = $observer->getCollection();
        $locationId = $observer->getLocation();

        $isNewWebposVersion = $observer->getData('is_new');
        $warehouseId = $locationId;
        // get warehouse id from location id when use with webpos old version
        if(!$isNewWebposVersion) {
            $warehouseId = $this->_mappingManagement->getWarehouseIdByLocationId($locationId);
        }
        if (!$warehouseId) {
            return $this;
        }
        $collection->getSelect()->joinInner(
            ['warehouse_product' => $collection->getTable(WarehouseProductResource::MAIN_TABLE)],
            'e.entity_id = warehouse_product.product_id 
            AND warehouse_product.' . WarehouseProductInterface::WAREHOUSE_ID . '=' . $warehouseId,
            ['qty']
        );

//        $productsInWarehouse = $this->_mappingManagement->getProductIdsByLocationId($locationId);
//        $collection->addFieldToFilter('entity_id', ['in' => $productsInWarehouse]);

        return $this;
    }

}