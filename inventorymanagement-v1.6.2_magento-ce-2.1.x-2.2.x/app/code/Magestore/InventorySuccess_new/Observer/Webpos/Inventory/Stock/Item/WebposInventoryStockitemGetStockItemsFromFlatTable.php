<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Webpos\Inventory\Stock\Item;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magestore\InventorySuccess\Api\Warehouse\Location\MappingManagementInterface;

class WebposInventoryStockitemGetStockItemsFromFlatTable implements ObserverInterface
{
    /**
     * @var MappingManagementInterface
     */
    protected $_mappingManagement;
    /**
     * WebposLocationSaveAfter constructor.
     * @param MappingManagementInterface $mappingManagement
     */
    public function __construct(
        MappingManagementInterface $mappingManagement
    )
    {
        $this->_mappingManagement = $mappingManagement;
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
        $warehouseId = $warehouseId ? $warehouseId : 0;

        $where = $object->getData();
        $where[] = "(e.website_id = ". $warehouseId .")";
        $object->setData($where);

        return $this;
    }
}