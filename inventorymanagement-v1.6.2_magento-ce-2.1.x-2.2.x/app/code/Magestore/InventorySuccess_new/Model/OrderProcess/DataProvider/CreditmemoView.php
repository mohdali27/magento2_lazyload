<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\OrderProcess\DataProvider;


class CreditmemoView
{   
    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\CreditmemoItemManagementInterface 
     */
    protected $creditmemoItemManagement;    
    
    /**
     * @var \Magestore\InventorySuccess\Model\WarehouseFactory 
     */
    protected $warehouseFactory;    
    
    /**
     * 
     * @param \Magestore\InventorySuccess\Api\Warehouse\CreditmemoItemManagementInterface $creditmemoItemManagement
     * @param \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory
     */
    public function __construct(
        \Magestore\InventorySuccess\Api\Warehouse\CreditmemoItemManagementInterface $creditmemoItemManagement,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory
    )
    {
        $this->creditmemoItemManagement = $creditmemoItemManagement;   
        $this->warehouseFactory = $warehouseFactory;
    }
    
    /**
     * Get Shipped Warehouse
     * 
     * @param int $itemId
     * 
     * @return \Magestore\InventorySuccess\Model\Warehouse
     */
    public function getReturnWarehouse($itemId)
    {
        $warehouse = $this->warehouseFactory->create();
        $warehouseId = $this->creditmemoItemManagement->getWarehouseByCreditmemoItemId($itemId);
        if($warehouseId) {
            $warehouse->load($warehouseId);
        }
        return $warehouse;
    }
    
}