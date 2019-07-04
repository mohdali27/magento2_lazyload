<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\WarehouseStoreViewMap;

use Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface;
use Magento\Store\Model\Store;

/**
 * Class WarehouseStoreViewMapManagement
 * @package Magestore\InventorySuccess\Model\Warehouse
 */
class WarehouseStoreViewMapManagement
{
    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\WarehouseStoreViewMap\CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\WarehouseStoreViewMap\CollectionFactory $collectionFactory
    )
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     *
     * @param \Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface $warehouse
     * @param array $storeId
     */
    public function linkWarehouseToStores($warehouse, $storeIds = null)
    {
        $storeIds = $storeIds ? $storeIds : [];
        $warehouseId = $warehouse->getWarehouseId();
        $existedStoreIds = [];
        /** @var \Magestore\InventorySuccess\Model\ResourceModel\WarehouseStoreViewMap\Collection $collection */
        $collection = $this->collectionFactory->create()
            ->addFieldToFilter(
                [Store::STORE_ID, WarehouseInterface::WAREHOUSE_ID],
                [
                    ['in' => $storeIds],
                    ['eq' => $warehouseId]
                ]
            );
        if ($collection->getSize()) {
            /** @var \Magestore\InventorySuccess\Model\WarehouseStoreViewMap $map */
            foreach ($collection as $map) {
                $existedStoreIds[] = $map->getStoreId();
                if ($map->getWarehouseId() == $warehouseId) {
                    if (!in_array($map->getStoreId(), $storeIds))
                        $map->delete();
                    continue;
                }
                $map->setWarehouseId($warehouseId)->save();
            }
        }
        $storeIds = array_diff($storeIds, $existedStoreIds);
        if (!empty($storeIds))
            $collection->getResource()->linkWarehouseToStores($warehouseId, $storeIds);
    }
}
