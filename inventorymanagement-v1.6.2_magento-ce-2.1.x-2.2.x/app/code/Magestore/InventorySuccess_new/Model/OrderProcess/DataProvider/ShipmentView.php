<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\OrderProcess\DataProvider;


class ShipmentView
{   
    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\ShipmentItemManagementInterface 
     */
    protected $shipmentItemManagement;    
    
    /**
     * @var \Magestore\InventorySuccess\Model\WarehouseFactory 
     */
    protected $warehouseFactory;    
    
    /**
     * 
     * @param \Magestore\InventorySuccess\Api\Warehouse\ShipmentItemManagementInterface $shipmentItemManagement
     * @param \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory
     */
    public function __construct(
        \Magestore\InventorySuccess\Api\Warehouse\ShipmentItemManagementInterface $shipmentItemManagement,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory
    )
    {
        $this->shipmentItemManagement = $shipmentItemManagement;   
        $this->warehouseFactory = $warehouseFactory;
    }
    
    /**
     * Get Shipped Warehouse
     * 
     * @param int $shipmentId
     * 
     * @return \Magestore\InventorySuccess\Model\Warehouse
     */
    public function getShipWarehouse($shipmentId)
    {
        $warehouse = $this->warehouseFactory->create();
        $warehouseId = $this->shipmentItemManagement->getWarehouseByShipmentId($shipmentId);
        if($warehouseId) {
            $warehouse->load($warehouseId);
        }
        return $warehouse;
    }
    
}