<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\OrderProcess\DataProvider;

class CreditmemoForm
{
    /**
     * @var \Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface
     */
    protected $orderProcessService;
    
    /**
     * CreditmemoForm constructor.
     * @param \Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface $orderProcessService
     */
    public function __construct(
        \Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface $orderProcessService
    )
    {
        $this->orderProcessService = $orderProcessService;
    }

    /**
     * Get list of available warehouses to return items
     *
     * @return array
     */
    public function getAvailableWarehouses()
    {
        $availableWarehouses = [];
        $warehouses = $this->orderProcessService->getCreditmemoWarehouseList();
        if ($warehouses->getSize()) {
            foreach ($warehouses as $warehouse) {
                $availableWarehouses[$warehouse->getId()] = $warehouse->getWarehouseName() . ' (' . $warehouse->getWarehouseCode() . ')';
            }
        }
        return $availableWarehouses;
    }

}
