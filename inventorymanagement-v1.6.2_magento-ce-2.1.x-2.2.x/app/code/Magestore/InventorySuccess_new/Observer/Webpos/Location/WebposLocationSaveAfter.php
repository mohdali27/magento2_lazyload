<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Webpos\Location;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magestore\InventorySuccess\Api\Warehouse\Location\MappingManagementInterface;

class WebposLocationSaveAfter implements ObserverInterface
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
        $location = $observer->getDataObject();
        $warehouseId = $location->getWarehouseId();
        if($warehouseId == null || !$location->getLocationId()){
            return $this;
        }
        $this->_mappingManagement->mappingWarehouseToLocation($warehouseId,$location->getLocationId());
        return $this;
    }
}