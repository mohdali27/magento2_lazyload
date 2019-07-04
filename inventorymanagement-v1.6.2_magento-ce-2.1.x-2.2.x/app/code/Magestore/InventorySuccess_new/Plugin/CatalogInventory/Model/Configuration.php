<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Plugin\CatalogInventory\Model;

use Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface;

class Configuration
{
    /**
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     *
     * @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface;
     */
    protected $warehouseManagement;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;


    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        WarehouseManagementInterface $warehouseManagement
    )
    {
        $this->authSession = $authSession;
        $this->eventManager = $eventManager;
        $this->warehouseManagement = $warehouseManagement;
    }

    /**
     *
     * @param \Magento\CatalogInventory\Model\Configuration $stockConfiguration
     * @param int $scopeId
     * @return int
     */
    public function afterGetDefaultScopeId(\Magento\CatalogInventory\Model\Configuration $stockConfiguration, $scopeId)
    {
        $scope = new \Magento\Framework\DataObject(['scope_id' => $scopeId]);
        if ($this->warehouseManagement->isGetStockFromWarehouse()) {
            $warehouse = $this->warehouseManagement->getCurrentWarehouseByStore();
            if ($warehouse->getId()) {
                $scopeId = $warehouse->getId();
                $scope->setData('scope_id', $scopeId);
            }
        }
        $this->eventManager->dispatch('inventorysuccess_get_default_scope_id', ['scope' => $scope]);
        return $scope->getData('scope_id');
    }

}