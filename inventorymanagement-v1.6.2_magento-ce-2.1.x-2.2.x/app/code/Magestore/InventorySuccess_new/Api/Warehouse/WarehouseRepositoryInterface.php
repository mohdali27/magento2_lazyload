<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Warehouse;

interface WarehouseRepositoryInterface
{
    /**
     * Get warehouse information
     *
     * @param string $warehouseCode
     * @return \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($warehouseCode);

    /**
     * Performs persist operations for a specified warehouse.
     *
     * @param \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface $warehouse The warehouse ID.
     * @return \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface Warehouse interface.
     */
    public function create(\Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface $warehouse);

    /**
     * @param string $code
     * @param \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface $warehouse
     * @return \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface Warehouse interface.
     */
    public function update($code, \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface $warehouse);
}