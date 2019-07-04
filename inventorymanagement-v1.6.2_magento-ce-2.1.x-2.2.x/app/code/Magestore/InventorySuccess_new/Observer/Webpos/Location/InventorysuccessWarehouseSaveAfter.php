<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Webpos\Location;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magestore\InventorySuccess\Api\Warehouse\Location\MappingManagementInterface;
use Magento\Framework\ObjectManagerInterface;

class InventorysuccessWarehouseSaveAfter implements ObserverInterface
{
    /**
     * @var MappingManagementInterface
     */
    protected $_mappingManagement;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magestore\Webpos\Model\Location\Location
     */
    protected $_location;

    /**
     * InventorysuccessWarehouseSaveAfter constructor.
     * @param MappingManagementInterface $mappingManagement
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        MappingManagementInterface $mappingManagement,
        ObjectManagerInterface $objectManager
    )
    {
        $this->_mappingManagement = $mappingManagement;
        $this->_objectManager = $objectManager;
        $this->_moduleManager = $this->_objectManager->create('Magento\Framework\Module\Manager');
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        if ($this->_moduleManager->isOutputEnabled('Magestore_Webpos')) {
            try {
                $this->_objectManager->get('Magestore\Webpos\Model\Location\Location');
            } catch (\Exception $ex) {
                return $this;
            }
        } else {
            return $this;
        }
        $warehouse = $observer->getDataObject();
        $locationId = $warehouse->getLocationId();
        if (!$locationId || !$warehouse->getWarehouseId()) {
            return $this;
        }
        $this->_mappingManagement->mappingWarehouseToLocation($warehouse->getWarehouseId(),$locationId);
        return $this;
    }
}