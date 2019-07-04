<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Source\Adminhtml;

use Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface;

/**
 * Class Warehouse
 * @package Magestore\InventorySuccess\Model\Source\Adminhtml
 */
class Warehouse implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Warehouse model
     *
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory
     */
    protected $warehouseCollectionFactory;


    /**
     * @var PermissionManagementInterface
     */
    protected $permissionManagementInterface;


    /**
     * Warehouse constructor.
     * @param \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection $warehouseCollection
     * @param PermissionManagementInterface $permissionManagementInterface
     */
    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory $warehouseCollectionFactory,
        PermissionManagementInterface $permissionManagementInterface
    ) {
        $this->warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->permissionManagementInterface = $permissionManagementInterface;
    }

    /**
     * @return array
     */
    public function toOptionArray($permissionResource = null)
    {
        $permissionResource = $permissionResource ? $permissionResource : 'Magestore_InventorySuccess::warehouse_view';
        $collection = $this->warehouseCollectionFactory->create();
        $collection = $this->permissionManagementInterface->filterPermission($collection, $permissionResource);
        $options = [];
        if (count($collection) > 0) {
            foreach ($collection as $item) {
                $label = $item->getData('warehouse_name') ? $item->getData('warehouse_name') . '('. $item->getData('warehouse_code') . ')' : $item->getData('warehouse_id'). '('. $item->getData('warehouse_code') . ')';
                $options[] = array('value' => $item->getId(), 'label' => $label);
            }
        }
        return $options;
    }

    /**
     * @return array
     */
    public function toOptionArrayTest()
    {
        $options = $this->warehouseCollection->toOptionArray();
        return $options;
    }

}
