<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Warehouse;

use Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product as WarehouseProductResource;

class WarehouseStockRegistry implements WarehouseStockRegistryInterface
{

    /**
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magestore\InventorySuccess\Model\Warehouse\ProductFactory
     */
    protected $_warehouseProductFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\CollectionFactory
     */
    protected $_warehouseProductCollectionFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\WarehouseStockRegistryFactory
     */
    protected $resourceFactory;

    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface
     */
    protected $warehouseManagement;

    /**
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\InventorySuccess\Model\Warehouse\ProductFactory $warehouseProductFactory,
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\CollectionFactory $warehouseProductCollectionFactory,
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\WarehouseStockRegistryFactory $resourceFactory,
        \Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface $warehouseManagement
    )
    {
        $this->_objectManager = $objectManager;
        $this->_warehouseProductFactory = $warehouseProductFactory;
        $this->_warehouseProductCollectionFactory = $warehouseProductCollectionFactory;
        $this->resourceFactory = $resourceFactory;
        $this->warehouseManagement = $warehouseManagement;
    }

    /**
     * @return \Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface
     */
    public function getStock($warehouseId, $productId)
    {
        $warehouseProductCollection = $this->_warehouseProductCollectionFactory->create()->selectAllStocks();
        $warehouseProduct = $warehouseProductCollection
            ->addFieldToFilter(WarehouseProductInterface::WAREHOUSE_ID, $warehouseId)
            ->addFieldToFilter(WarehouseProductInterface::PRODUCT_ID, $productId)
            ->setPageSize(1)
            ->setCurPage(1);
        return $warehouseProduct->getFirstItem();
    }

    /**
     * @inheritdoc
     */
    public function getStocks($warehouseId, $productIds = [])
    {
        $warehouseProductCollection = $this->_warehouseProductCollectionFactory->create();
        if ($warehouseId) {
            $warehouseProductCollection->addFieldToFilter(WarehouseProductInterface::WAREHOUSE_ID, $warehouseId);
        }
        if (count($productIds)) {
            $warehouseProductCollection->addFieldToFilter(WarehouseProductInterface::PRODUCT_ID, ['in' => $productIds]);
        }
        return $warehouseProductCollection;
    }

    /**
     * get Stocks from enable Warehouses
     *
     * @param array $productIds
     * @param array $warehouseIds
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection
     */
    public function getStocksFromEnableWarehouses($productIds = [], $warehouseIds = [])
    {
//        $enableWarehouseIds = $this->warehouseManagement->getEnableWarehouses()->getAllIds();
        $warehouseProductCollection = $this->_warehouseProductCollectionFactory->create();
//            ->addFieldToFilter(WarehouseProductInterface::WAREHOUSE_ID, ['in' => $enableWarehouseIds]);
        if (count($productIds))
            $warehouseProductCollection->addFieldToFilter(WarehouseProductInterface::PRODUCT_ID, ['in' => $productIds]);
        if (count($warehouseIds))
            $warehouseProductCollection->addFieldToFilter(WarehouseProductInterface::WAREHOUSE_ID, ['in' => $warehouseIds]);
        return $warehouseProductCollection;
    }

    /**
     * @inheritdoc
     */
    public function removeProduct($warehouseId, $productId)
    {
        $warehouseProduct = $this->getStock($warehouseId, $productId);
        if ($warehouseProduct->getId()) {
            if ($warehouseProduct->getTotalQty() == 0 && $warehouseProduct->getQtyToShip() == 0) {
                $warehouseProduct->delete();
            } else {
                throw new \Exception(__('Can\'t remove this product with qty more than 0 from a Location.'));
            }
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStockWarehouses($productId)
    {
        $warehouseProductCollection = $this->_warehouseProductCollectionFactory->create();
        return $warehouseProductCollection->retrieveWarehouseStocks($productId);
    }


    /**
     * @inheritdoc
     */
    public function getStocksWarehouses($productIds)
    {
        $warehouseProductCollection = $this->_warehouseProductCollectionFactory->create();
        return $warehouseProductCollection->retrieveWarehouseStocksByProductIds($productIds);
    }

    /**
     * @inheritdoc
     */
    public function removeProducts($warehouseId, $productIds)
    {
        $success = [];
        $error = [];
        foreach ($productIds as $productId) {
            $warehouseProduct = $this->getStock($warehouseId, $productId);
            if ($warehouseProduct->getId()) {
                if ($warehouseProduct->getTotalQty() == 0 && $warehouseProduct->getQtyToShip() == 0) {
                    $this->deleteWarehouseProduct($warehouseProduct);
                    $success[] = $productId;
                } else {
                    $error[] = $productId;
                }
            }
        }
        return ['success' => $success, 'error' => $error];
    }

    public function deleteWarehouseProduct($warehouseProduct)
    {
        $warehouseProduct->delete();
    }

    /**
     * Update shelf location in Warehouse
     *
     * @param int $warehouseId
     * @param array $locations
     */
    public function updateLocation($warehouseId, $locations)
    {
        $this->getResource()->updateLocation($warehouseId, $locations);
        return $this;
    }


    /**
     * Force edit available qty in Location
     *
     * @param int $warehouseId
     * @param array $availQty
     */

    public function forceEditAvailableQty($warehouseId, $availQty)
    {
        $this->getResource()->forceEditAvailableQty($warehouseId, $availQty);
        return $this;
    }


    /**
     * Prepare query to change total_qty, qty_to_ship of product in warehouse
     * Do not update global stock
     * Do not commit query
     *
     * @param int $warehouseId
     * @param int $productId
     * @param array $changeQtys
     * @return array
     */
    public function prepareChangeProductQty($warehouseId, $productId, $changeQtys)
    {
        $stock = $this->getStock($warehouseId, $productId);
        $queries = [];
        if (!$stock->getId()) {
            $queries[] = $this->getResource()->prepareAddProductToWarehouse($warehouseId, $productId, $changeQtys);
        }
        $queries[] = $this->getResource()->prepareChangeProductQty($warehouseId, $productId, $changeQtys);
        return $queries;
    }


    /**
     * Prepare query to mass change total_qty, qty_to_ship of products in warehouse
     * Do not update global stock
     * Do not commit query
     * $changeQtys[$warehouseId => [$productId => ['total_qty' => 2]]]
     *
     * @param array $changeQtys
     * @return array
     */
    public function prepareMassChangeProductQty($changeQtys)
    {
        return $this->getResource()->prepareMassChangeProductQty($changeQtys);
    }


    /**
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\WarehouseStockRegistry
     */
    public function getResource()
    {
        return $this->resourceFactory->create();
    }

    /**
     *
     * @param int $productId
     * @param int $storeId
     * @return \Magento\Framework\DataObject
     */
    public function getStockItem($productId, $storeId)
    {
        $warehouse = $this->_objectManager->get('Magestore\InventorySuccess\Model\Warehouse')
            ->getCollection()
            ->addFieldToFilter(\Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface::STORE_ID, $storeId)
            ->setPageSize(1)->setCurPage(1)
            ->getFirstItem();

        if ($warehouse->getId()) {
            $stock = $this->getStock($warehouse->getId(), $productId);
            $stockItem = new \Magento\Framework\DataObject([
                'qty' => $stock->getTotalQty() - $stock->getQtyToShip(),
            ]);
            return $stockItem;
        }
        return null;
    }

    /**
     *
     * @param int $productId
     * @return \Magento\Framework\DataObject
     */
    public function getStoreDataFromCurrentStore($productId)
    {
        $warehouse = $this->warehouseManagement->getCurrentWarehouseByStore();

        $stock = $this->getStock($warehouse->getId(), $productId);

        $stockItem = new \Magento\Framework\DataObject([
            'qty' => $stock->getTotalQty() - $stock->getQtyToShip(),
        ]);
        return $stockItem;
    }

    /**
     *
     * @param int $productId
     * @param array $stockItemData
     * @param array $warehouses
     * @param array $ignoreWarehouses
     */
    public function cloneStockItemData($productId, $stockItemData, $warehouses = [], $ignoreWarehouses = [])
    {
        $this->getReSource()->cloneStockItemData($productId, $stockItemData, $warehouses, $ignoreWarehouses);
    }

    /**
     *
     * @param int $productId
     * @param array $stockStatusData
     * @param array $warehouses
     * @param array $ignoreWarehouses
     */
    public function cloneStockStatus($productId, $stockStatusData, $warehouses = [], $ignoreWarehouses = [])
    {
        $this->getReSource()->cloneStockStatus($productId, $stockStatusData, $warehouses, $ignoreWarehouses);
    }


    /**
     * prepare to change qtys of product in multiple warehouses
     *
     * @param int $productId
     * @param array $changeQtys
     * @return array
     */
    public function prepareChangeQtys($productId, $changeQtys)
    {
        $queries = array();
        $changeQtys = $this->prepareChangeQtysData($changeQtys);
        if (count($changeQtys)) {
            foreach ($changeQtys as $warehouseId => $changeQtyData) {
                if ($warehouseId === null) {
                    continue;
                }
                $stock = $this->getStock($warehouseId, $productId, true);
                if (!$stock->getId()) {
                    $queries[] = $this->getResource()->prepareAddProductToWarehouse($warehouseId, $productId, $changeQtyData);
                } else {
                    $queries[] = $this->getResource()->prepareChangeProductQty($warehouseId, $productId, $changeQtyData);
                }
            }
        }
        return $queries;
    }


    /**
     *
     * @param array $changeQtys
     * @return array
     */
    protected function prepareChangeQtysData($changeQtys)
    {
        if (count($changeQtys)) {
            foreach ($changeQtys as $warehouseId => $changeQtyData) {
                if (!count($changeQtyData)) {
                    unset($changeQtys[$warehouseId]);
                    continue;
                }
                foreach ($changeQtyData as $field => $value) {
                    if ($value == 0) {
                        unset($changeQtyData[$field]);
                    }
                }
            }
        }
        return $changeQtys;
    }

}