<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\OrderProcess;

use Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface;
use Magestore\InventorySuccess\Model\Warehouse;

class OrderProcessService implements OrderProcessServiceInterface
{
    /**
     * @var \Magestore\InventorySuccess\Model\Permission\PermissionManagement
     */
    protected $permissionManagement;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory
     */
    protected $warehouseCollectionFactory;

    /**
     * OrderProcessService constructor.
     * @param \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagement
     * @param \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory
     */
    public function __construct(
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagement,
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory
    )
    {
        $this->permissionManagement = $permissionManagement;
        $this->warehouseCollectionFactory = $warehouseCollectionFactory;
    }

    /**
     * Check current user is allowed to change warehouse for order
     *
     * @return bool|mixed
     */
    public function canChangeOrderWarehouse()
    {
        return $this->permissionManagement->checkPermission(self::CHANGE_WAREHOUSE_PERMISSION);
    }

    /**
     * Check current user is allow to view this warehouse order
     *
     * @param Warehouse|null $warehouse
     * @return bool|mixed
     */
    public function canViewWarehouse(Warehouse $warehouse = null)
    {
        return $this->permissionManagement->checkPermission(self::VIEW_ORDER_WAREHOUSE_PERMISSION, $warehouse);
    }

    /**
     * Get List warehouse are allowed to view by current user
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection
     */
    public function getViewWarehouseList()
    {
        $warehouseCollection = $this->warehouseCollectionFactory->create();
        if ($this->canChangeOrderWarehouse())
            return $warehouseCollection;
        $this->permissionManagement->filterPermission($warehouseCollection, self::VIEW_ORDER_WAREHOUSE_PERMISSION);
        return $warehouseCollection;
    }

    /**
     * Get List warehouse are allowed to create shipment by current user
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection
     */
    public function getShipmentWarehouseList()
    {
        $warehouseCollection = $this->warehouseCollectionFactory->create();
        $this->permissionManagement->filterPermission($warehouseCollection, self::CREATE_SHIPMENT_WAREHOUSE_PERMISSION);
        return $warehouseCollection;
    }

    /**
     * Check current user is allowed to create shipment for warehouse
     * 
     * @param Warehouse|null $warehouse
     * @return bool|mixed
     */
    public function canCreateShipment(Warehouse $warehouse = null)
    {
        if ($this->isAllPermission($warehouse))
            return true;
        return $this->permissionManagement->checkPermission(self::CREATE_SHIPMENT_WAREHOUSE_PERMISSION, $warehouse);
    }

    /**
     * Get List warehouse are allowed to create credit memo by current user
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection
     */
    public function getCreditmemoWarehouseList()
    {
        $warehouseCollection = $this->warehouseCollectionFactory->create();
        $this->permissionManagement->filterPermission($warehouseCollection, self::CREATE_CREDITMEMO_WAREHOUSE_PERMISSION);
        return $warehouseCollection;
    }

    /**
     * Check current user is allowed to create credit memo for warehouse
     *
     * @param Warehouse|null $warehouse
     * @return bool|mixed
     */
    public function canCreateCreditmemo(Warehouse $warehouse = null)
    {
        if ($this->isAllPermission($warehouse))
            return true;
        return $this->permissionManagement->checkPermission(self::CREATE_CREDITMEMO_WAREHOUSE_PERMISSION, $warehouse);
    }

    /**
     * Check current user has all permission
     *
     * @return bool|mixed
     */
    public function isAllPermission(Warehouse $warehouse = null)
    {
        if (!$warehouse || !$warehouse->getId())
            return $this->permissionManagement->checkPermission('Magento_Backend::all');
        return false;
    }
}
