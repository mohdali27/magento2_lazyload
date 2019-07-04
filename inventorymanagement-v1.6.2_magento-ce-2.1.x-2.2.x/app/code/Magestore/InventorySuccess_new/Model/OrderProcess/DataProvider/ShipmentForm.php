<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\OrderProcess\DataProvider;

use Magento\Framework\Cache\Frontend\Adapter\Zend;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;

class ShipmentForm
{

    /**
     * @var \Magestore\InventorySuccess\Model\WarehouseFactory
     */
    protected $warehouseFactory;

    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface
     */
    protected $warehouseManagement;

    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface
     */
    protected $warehouseStockRegistry;

    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\OrderItemManagementInterface
     */
    protected $orderItemManagement;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\CollectionFactory
     */
    protected $warehouseProductCollectionFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\Warehouse\ProductFactory
     */
    protected $warehouseProductFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $catalogInventoryConfiguration;

    /**
     * @var \Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface
     */
    protected $orderProcessService;


    /**
     * ShipmentForm constructor.
     * @param \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory
     * @param \Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface $warehouseManagement
     * @param \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegistry
     * @param \Magestore\InventorySuccess\Api\Warehouse\OrderItemManagementInterface $orderItemManagement
     * @param \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\CollectionFactory $warehouseProductCollectionFactory
     * @param \Magestore\InventorySuccess\Model\Warehouse\ProductFactory $warehouseProductFactory
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $catalogInventoryConfiguration
     * @param \Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface $orderProcessService
     */
    public function __construct(
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        \Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface $warehouseManagement,
        \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegistry,
        \Magestore\InventorySuccess\Api\Warehouse\OrderItemManagementInterface $orderItemManagement,
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\CollectionFactory $warehouseProductCollectionFactory,
        \Magestore\InventorySuccess\Model\Warehouse\ProductFactory $warehouseProductFactory,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $catalogInventoryConfiguration,
        \Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface $orderProcessService
    )
    {
        $this->warehouseFactory = $warehouseFactory;
        $this->warehouseManagement = $warehouseManagement;
        $this->warehouseStockRegistry = $warehouseStockRegistry;
        $this->orderItemManagement = $orderItemManagement;
        $this->warehouseProductCollectionFactory = $warehouseProductCollectionFactory;
        $this->warehouseProductFactory = $warehouseProductFactory;
        $this->catalogInventoryConfiguration = $catalogInventoryConfiguration;
        $this->orderProcessService = $orderProcessService;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getAvailableWarehouses($order)
    {
        /* prepare list of items to ship */
        $needToShipItems = $this->_prepareNeedToShipItems($order);

        /* get orderred Warehouses from items */
        $orderWarehouses = $this->_loadOrderWarehouses($needToShipItems);

        $canShipWarehouses = $this->orderProcessService->getShipmentWarehouseList();

        /* get products of items in all warehouses */
        $whProducts = $this->warehouseStockRegistry
            ->getStocksFromEnableWarehouses(array_keys($needToShipItems), $canShipWarehouses->getAllIds());

        /*Get stock items are not manage stock*/
        $warehouseProductIds = $whProducts->getColumnValues('product_id');
        $notInWarehouseProductIds = array_diff(array_keys($needToShipItems), $warehouseProductIds);

        $notManageStocksItem = $this->getNotManageStockItems($notInWarehouseProductIds);
        if ($notManageStocksItem->count() > 0) {
            $enableWarehouseIds = $this->warehouseManagement->getEnableWarehouses()->getAllIds();
            foreach ($notManageStocksItem as $item) {
                $this->addNotManageStockItemToShip($whProducts, $item, $enableWarehouseIds);
            }
        }

        /* load information of warehouses */
        $warehouseIds = [];
        foreach ($whProducts as $whProduct) {
            $warehouseIds[$whProduct->getWarehouseId()] = $whProduct->getWarehouseId();
        }

        $warehouseList = $this->warehouseManagement->getWarehouses($warehouseIds);

        /* prepare list of available warehouses */
        $warehouses = $this->_prepareAvailableWarehouses($needToShipItems, $whProducts, $orderWarehouses, $warehouseList);

        /* scan need-to-ship items before returning */
        $warehouses = $this->_scanShipItemsInWarehouseList($warehouses, $needToShipItems);

        return $this->_sortWarehouses($warehouses);
    }

    /**
     * get Stocks of not manage stock items
     *
     * @param array $productIds
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection
     */
    protected function getNotManageStockItems($productIds)
    {
        $warehouseProductCollection = $this->warehouseProductCollectionFactory->create()
            ->selectAllStocks()
            ->addFieldToFilter(WarehouseProductInterface::WAREHOUSE_ID, WarehouseProductInterface::DEFAULT_SCOPE_ID)
//            ->addFieldToFilter(\Magento\CatalogInventory\Api\Data\StockItemInterface::MANAGE_STOCK, 0)
            ->addFieldToFilter(WarehouseProductInterface::PRODUCT_ID, ['in' => $productIds]);
        return $warehouseProductCollection;
    }

    /**
     * Add item not manage stock into warehouse product to ship
     *
     * @param \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection $whProducts
     * @param \Magestore\InventorySuccess\Model\Warehouse\Product $item
     * @param array $warehouseIds
     */
    protected function addNotManageStockItemToShip($whProducts, $item, $warehouseIds)
    {
        if ($this->isManageStock($item))
            return $whProducts;
        foreach ($warehouseIds as $warehouseId) {
            $stockItem = $this->warehouseProductFactory->create();
            $stockItem->addData($item->getData());
            $stockItem->setId('99999999' . $item->getProductId() . $warehouseId);
            $stockItem->setWarehouseId($warehouseId);
            $stockItem->setQty('9999999');
            $stockItem->setTotalQty('9999999');
            $whProducts->addItem($stockItem);
        }
        return $whProducts;
    }

    /**
     * Manage stock of product in this item or not
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     */
    public function isManageStock($stockItem)
    {
        if (!$stockItem->getUseConfigManageStock() && !$stockItem->getManageStock()) {
            return false;
        }
        if ($stockItem->getUseConfigManageStock() && !$this->catalogInventoryConfiguration->getManageStock()) {
            return false;
        }
        return true;
    }

    /**
     * prepare list of available warehouses
     *
     * @param array $needToShipItems
     * @param \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection $whProducts
     * @param array $orderWarehouses
     * @param array $warehouseList
     * @return array
     */
    protected function _prepareAvailableWarehouses($needToShipItems, $whProducts, $orderWarehouses, $warehouseList)
    {
        $warehouses = [];
        foreach ($whProducts as $whProduct) {
            if (!$this->isManageStock($whProduct)) {
                $whProduct->setQty('9999999');
                $whProduct->setTotalQty('9999999');
            }
            $items = $needToShipItems[$whProduct->getProductId()];
            /** @var \Magento\Sales\Model\Order\Item $item */
            foreach ($items as $item) {
                if ($item->getProductType() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
                    continue;
                }
                $warehouseId = $whProduct->getWarehouseId();
                /* get orderred warehouseId */
                $orderWarehouseId = isset($orderWarehouses[$item->getItemId()]) ? $orderWarehouses[$item->getItemId()] : null;

                if ($warehouseId == $orderWarehouseId) {
                    /* create shipment from orderred Warehouse */
                    $qtyInWarehouse = floatval($whProduct->getTotalQty());
                } else {
                    /* create shipment from other warehouse */
                    $qtyInWarehouse = floatval($whProduct->getTotalQty() - $whProduct->getQtyToShip());
                }

                if ($item->getParentItemId() && $item->getParentItem()->getProductType() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
                    $parentItem = $item->getParentItem();
                    $parentAvailableQty = $parentItem->getQtyOrdered() > 0 && $qtyInWarehouse > 0 ? intval($qtyInWarehouse / ($item->getQtyOrdered() / $parentItem->getQtyOrdered())) : 0;
                    if (isset($warehouses[$warehouseId]['items'][$item->getParentItemId()]['qty_in_warehouse'])){
                        $parentAvailableQty = min($parentAvailableQty, $warehouses[$warehouseId]['items'][$item->getParentItemId()]['qty_in_warehouse']);
                    }
                    $qtyToShip = $parentItem->getQtyOrdered() - $parentItem->getQtyShipped() - $parentItem->getQtyRefunded() - $parentItem->getQtyCanceled();
                    $lackQty = max($qtyToShip - $parentAvailableQty, 0);
                    $warehouses[$warehouseId]['items'][$item->getParentItemId()] = [
                        'qty_in_warehouse' => $parentAvailableQty,
                        'lack_qty' => $lackQty,
                    ];

                    $qtyToShip = $parentItem->getQtyOrdered()!=0?$qtyToShip * $item->getQtyOrdered() / $parentItem->getQtyOrdered():0;
                    $lackQty = max($qtyToShip - $qtyInWarehouse, 0);

                    $itemId = $this->_getItemIdToShip($item);
                    $warehouses[$warehouseId]['items'][$itemId] = [
                        'qty_in_warehouse' => $qtyInWarehouse,
                        'lack_qty' => $lackQty,
                    ];
                    /* insert warehouse data to array */
                    if (isset($warehouses[$warehouseId]['lack_qty'])) {
                        $warehouses[$warehouseId]['lack_qty'] += $lackQty;
                    } else {
                        $warehouses[$warehouseId]['lack_qty'] = $lackQty;
                    }
                    $warehouses[$warehouseId]['info'] = $warehouseList[$warehouseId];
                } else {
                    if ($qtyInWarehouse <= 0){
                        continue;
                    }
                    $qtyToShip = $this->_getQtyToShip($item);
                    $itemId = $this->_getItemIdToShip($item);

                    $lackQty = max($qtyToShip - $qtyInWarehouse, 0);

                    $warehouses[$warehouseId]['items'][$itemId] = [
                        'qty_in_warehouse' => $qtyInWarehouse,
                        'lack_qty' => $lackQty,
                    ];

                    /* insert warehouse data to array */
                    if (isset($warehouses[$warehouseId]['lack_qty'])) {
                        $warehouses[$warehouseId]['lack_qty'] += $lackQty;
                    } else {
                        $warehouses[$warehouseId]['lack_qty'] = $lackQty;
                    }
                    $warehouses[$warehouseId]['info'] = $warehouseList[$warehouseId];
                }
            }
        }
        return $warehouses;
    }

    /**
     * prepare list of items to ship
     *
     * @param \Magento\Sales\Model\Order $order
     */
    protected function _prepareNeedToShipItems($order)
    {
        /* prepare list of items to ship */
        $needToShipItems = [];
        foreach ($order->getAllItems() as $item) {
            if ($item->getRealProductType() !== null) {
                /* composite product */
                continue;
            }
            $needToShip = true;
            if ($item->getQtyToShip() == 0) {
                if ($item->getParentItemId()) {
                    if (!$item->getParentItem()->getQtyToShip()) {
                        $needToShip = false;
                    }
                } else {
                    $needToShip = false;
                }
            }
            if (!$needToShip) {
                continue;
            }
            if (!isset($needToShipItems[$item->getProductId()])) {
                $needToShipItems[$item->getProductId()] = [$item];
            } else {
                $needToShipItems[$item->getProductId()][] = $item;
            }
        }
        return $needToShipItems;
    }

    /**
     *
     * @param array $needToShipItems
     * @return array
     */
    protected function _loadOrderWarehouses($needToShipItems)
    {
        $orderItemIds = [];
        foreach ($needToShipItems as $items) {
            foreach ($items as $item)
                $orderItemIds[] = $item->getItemId();
        }

        return $this->orderItemManagement->getWarehousesByItemIds($orderItemIds);
    }

    /**
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return boolean
     */
    protected function _isUsedParentItem($item)
    {
        if ($item->getParentItemId()) {
            if ($item->getParentItem()->getProductType() == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * scan need-to-ship items before returning
     *
     * @param array $warehouses
     * @param array $needToShipItems
     * @return array
     */
    protected function _scanShipItemsInWarehouseList($warehouses, $needToShipItems)
    {
        foreach ($warehouses as $warehouseId => &$warehouseData) {
            foreach ($needToShipItems as $items) {
                foreach ($items as $item) {
                    $qtyToShip = $this->_getQtyToShip($item);
                    $itemId = $this->_getItemIdToShip($item);
                    if (!isset($warehouseData['items'][$itemId])) {
                        $warehouseData['items'][$itemId] = [
                            'qty_in_warehouse' => 0,
                            'lack_qty' => $qtyToShip,
                        ];
                        $warehouses[$warehouseId]['lack_qty'] += $qtyToShip;
                    }
                }
            }
        }
        return $warehouses;
    }

    /**
     * Sort warehouses by lack_qty ASC
     *
     * @param array $warehouses
     * @return array
     */
    protected function _sortWarehouses($warehouses)
    {
        $sortedWarehouses = [];
        usort($warehouses, [$this, "sortShipmentWarehouses"]);
        foreach ($warehouses as $warehouse) {
            $warehouseId = $warehouse['info']['warehouse_id'];
            $sortedWarehouses[$warehouseId] = $warehouse;
        }
        return $sortedWarehouses;
    }

    /**
     * Compare lack_qty of warehouses
     *
     * @param array $warehouseA
     * @param array $warehouseB
     * @return int
     */
    public function sortShipmentWarehouses($warehouseA, $warehouseB)
    {
        if ($warehouseA['lack_qty'] == $warehouseB['lack_qty'])
            return 1;
        if ($warehouseA['lack_qty'] < $warehouseB['lack_qty'])
            return -1;
        return 1;
    }

    /**
     * Get Qty to Ship of Item
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return float
     */
    protected function _getQtyToShip($item)
    {
        if ($this->_isUsedParentItem($item)) {
            return $item->getParentItem()->getQtyToShip();
        }
        return $item->getQtyToShip();
    }

    /**
     * Get ItemId of need-to-ship item
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return int
     */
    protected function _getItemIdToShip($item)
    {
        if ($this->_isUsedParentItem($item)) {
            return $item->getParentItemId();
        }
        return $item->getItemId();
    }

}