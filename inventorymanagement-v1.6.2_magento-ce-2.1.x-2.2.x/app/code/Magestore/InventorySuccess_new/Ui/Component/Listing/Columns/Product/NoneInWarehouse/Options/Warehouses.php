<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\Component\Listing\Columns\Product\NoneInWarehouse\Options;

class Warehouses implements \Magento\Framework\Option\ArrayInterface{
    /**
     * @var \Magestore\Inventorysuccess\Model\WarehouseFactory
     */
    protected $warehouseFactory;
    
    /**
     * @var PermissionManagementInterface
     */
    protected $permissionManagementInterface;

    /**
     * @var array
     */
    protected $options;

    /**
     * Warehouses constructor.
     * @param \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory
     * @param \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagementInterface
     */
    public function __construct(
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        \Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface $permissionManagementInterface
        
    ) {
        $this->permissionManagementInterface = $permissionManagementInterface;
        $this->warehouseFactory = $warehouseFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $collection = $this->warehouseFactory->create()->getCollection();
        $collection = $this->permissionManagementInterface->filterPermission(
            $collection,'Magestore_InventorySuccess::product_none_in_warehouse');

        $warehouses = $collection->getItems();
        $options = [];
        $options[] = [
            'value' => '',
            'label' => __('-- Select a location --')
        ];
        foreach ($warehouses as $warehouse) {
            $options[] = [
                'value' => $warehouse['warehouse_id'],
                'label' => $warehouse['warehouse_name'] . '(' . $warehouse['warehouse_code'] . ')',
            ];
        }
        $this->options = $options;
        return $this->options;
    }
}
