<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Webpos\Inventory\Stock\Item;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magestore\InventorySuccess\Api\Warehouse\Location\MappingManagementInterface;
use Magestore\InventorySuccess\Model\ResourceModel\WarehouseLocationMap\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Link;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Bundle\Model\ResourceModel\Selection;

class WebposInventoryStockitemGetStockItems implements ObserverInterface
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
     * @var Collection
     */
    protected $_collection;
    /**
     * WebposLocationSaveAfter constructor.
     * @param MappingManagementInterface $mappingManagement
     */
    public function __construct(
        MappingManagementInterface $mappingManagement,
        Collection $collection,
        Link $productLink,
        Configurable $configurable,
        Selection $selection
    )
    {
        $this->_mappingManagement = $mappingManagement;
        $this->_collection = $collection;
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
        $collection = $observer->getcollection();
        $locationId = $observer->getLocation();
        if (!$observer->getWarehouseId()){
            $warehouseId = $this->_mappingManagement->getWarehouseIdByLocationId($locationId);
            $warehouseId = $warehouseId ? $warehouseId : 0;
        } else {
            $warehouseId = $observer->getWarehouseId();
        }
        $collection->getSelect()->where('stock_item_index.website_id = ?', $warehouseId);

        return $this;
    }
}