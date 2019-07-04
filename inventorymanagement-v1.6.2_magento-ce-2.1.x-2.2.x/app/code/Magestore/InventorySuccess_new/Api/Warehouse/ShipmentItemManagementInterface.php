<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Warehouse;

interface ShipmentItemManagementInterface
{
    /**
     * Get Warehouse by shipment id
     * 
     * @param int $shipmentId
     * @return int
     */
    public function getWarehouseByShipmentId($shipmentId);
  
}          