<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Webpos\Location;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magestore\InventorySuccess\Model\WarehouseLocationMapFactory;

class WebposLocationDeleteAfter implements ObserverInterface
{
    /**
     * @var WarehouseLocationMapFactory
     */
    protected $_warehouseLocationMap;

    /**
     * WebposLocationSaveAfter constructor.
     * @param MappingManagementInterface $mappingManagement
     */
    public function __construct(
        WarehouseLocationMapFactory $warehouseLocationMap
    )
    {
        $this->_warehouseLocationMap = $warehouseLocationMap;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $location = $observer->getDataObject();
        if(!$location->getLocationId()){
            return $this;
        }
        $this->_warehouseLocationMap->create()->load($location->getLocationId(),'location_id')->delete();
        return $this;
    }
}