<?php

namespace Magestore\InventorySuccess\Api\Warehouse;

interface WarehouseDuplicateServiceInterface {
    /**
     * @param int $id
     * @return \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface|bool
     */
    public function duplicateWarehouse($id);
}