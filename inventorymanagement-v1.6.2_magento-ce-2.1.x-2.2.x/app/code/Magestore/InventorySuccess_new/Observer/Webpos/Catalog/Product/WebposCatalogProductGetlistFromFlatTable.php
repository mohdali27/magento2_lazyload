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
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Bundle\Model\ResourceModel\Selection;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product as WarehouseProductResource;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;

class WebposCatalogProductGetlistFromFlatTable implements ObserverInterface
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
     * @var ProductCollection
     */
    protected $collection;

    /**
     * WebposCatalogProductGetlistFromFlatTable constructor.
     * @param MappingManagementInterface $mappingManagement
     * @param Link $productLink
     * @param Configurable $configurable
     * @param Selection $selection
     * @param ProductCollection $selection
     */
    public function __construct(
        MappingManagementInterface $mappingManagement,
        Link $productLink,
        Configurable $configurable,
        Selection $selection,
        ProductCollection $collection
    )
    {
        $this->_mappingManagement = $mappingManagement;
        $this->_productLink = $productLink;
        $this->_configurable = $configurable;
        $this->_selection = $selection;
        $this->collection = $collection;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $object = $observer->getObject();
        $locationId = $observer->getLocation();
        $warehouseId = $this->_mappingManagement->getWarehouseIdByLocationId($locationId);

        $object->setWarehouse($warehouseId);

        return $this;
    }
}