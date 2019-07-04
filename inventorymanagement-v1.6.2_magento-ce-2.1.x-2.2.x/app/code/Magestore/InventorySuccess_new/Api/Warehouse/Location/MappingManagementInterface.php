<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Warehouse\Location;

/**
 * Interface MappingManagementInterface
 * @package Magestore\InventorySuccess\Api\Warehouse\Location
 */
interface MappingManagementInterface
{
    /**
     * @param array $data
     * @return $this
     */
    public function createListMapping(array $data);

    /**
     * @param $warehouseId
     * @param $locationId
     * @param bool $force
     * @return bool
     */
    public function mappingWarehouseToLocation($warehouseId, $locationId, $force = false);

    /**
     * @param $locationId
     * @return array
     */
    public function getProductIdsByLocationId($locationId);

    /**
     * @param $locationId
     * @return mixed
     */
    public function getWarehouseIdByLocationId($locationId);
}          