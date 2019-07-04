<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Webpos\Inventory\Stock\Item;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magestore\InventorySuccess\Api\Data\AdjustStock\AdjustStockInterface;
use Magestore\InventorySuccess\Api\Warehouse\Location\MappingManagementInterface;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;
use Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface;
use Magestore\InventorySuccess\Api\Db\QueryProcessorInterface;
use Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface;
use Magestore\InventorySuccess\Model\AdjustStockFactory;
use Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface;

class WebposInventoryStockitemUpdate implements ObserverInterface
{
    /**
     * @var MappingManagementInterface
     */
    protected $_mappingManagement;

    /**
     * @var WarehouseStockRegistryInterface
     */
    protected $warehouseStockRegistry;

    /**
     * @var QueryProcessorInterface
     */
    protected $queryProcess;

    /**
     * @var AdjustStockManagementInterface
     */
    protected $adjustStockManagement;

    /**
     * @var AdjustStockFactory
     */
    protected $adjustStockFactory;

    /**
     * @var WarehouseManagementInterface
     */
    protected $warehouseManagement;

    /**
     * WebposLocationSaveAfter constructor.
     * @param MappingManagementInterface $mappingManagement
     */
    public function __construct(
        MappingManagementInterface $mappingManagement,
        WarehouseStockRegistryInterface $warehouseStockRegistry,
        QueryProcessorInterface $queryProcess,
        AdjustStockManagementInterface $adjustStockManagement,
        AdjustStockFactory $adjustStockFactory,
        WarehouseManagementInterface $warehouseManagement
    )
    {
        $this->_mappingManagement = $mappingManagement;
        $this->warehouseStockRegistry = $warehouseStockRegistry;
        $this->queryProcess = $queryProcess;
        $this->adjustStockManagement = $adjustStockManagement;
        $this->adjustStockFactory = $adjustStockFactory;
        $this->warehouseManagement = $warehouseManagement;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $stockItem = $observer->getEvent()->getStockItem();
        $locationId = $observer->getEvent()->getLocation();
        $changeQty = $observer->getEvent()->getChangeQty();
        $user = $observer->getEvent()->getUser();
        $warehouseId = $this->_mappingManagement->getWarehouseIdByLocationId($locationId);
        if ($stockItem->getData(WarehouseProductInterface::WAREHOUSE_ID) == $warehouseId) {
            /* updated stocks in warehouse, then update available_qty in warehouse */
            $qtyChanges = [
                $warehouseId => [
                    WarehouseProductInterface::AVAILABLE_QTY => (0 - $changeQty)
                ]
            ];
        } else {
            /* updated stocks in global, then update available_qty in global */
            $qtyChanges = [
                WarehouseProductInterface::DEFAULT_SCOPE_ID => [
                    WarehouseProductInterface::AVAILABLE_QTY => (0 - $changeQty)
                ]
            ];
        }
        if (!$warehouseId) {
            /* updated stocks in warehouse, then update available_qty in warehouse when not mapping warehouse-location*/
            $warehouseId = $this->warehouseManagement->getPrimaryWarehouse()->getWarehouseId();
            $productName = $stockItem->getName();
            $stockItem = $this->warehouseStockRegistry->getStock($warehouseId, $stockItem->getProductId());
            $stockItem->setName($productName);
        }
        /* prepare query then process query */
        $queries = $this->warehouseStockRegistry->prepareChangeQtys($stockItem->getProductId(), $qtyChanges);

        $this->queryProcess->start();
        $this->queryProcess->addQueries($queries);
        $this->queryProcess->process();

        /* create a new adjust stock */
        $this->adjustStock($stockItem, $changeQty, $warehouseId, $user);

        return $this;
    }

    /**
     * Create new adjutsStock to decrease total_qty in ordered Warehouse
     *
     * @param \Magento\CatalogInventory\Model\Stock\Item
     * @param float $changeQty
     * @param int $warehouseId
     * @param string $staff
     */
    protected function adjustStock($stockItem, $changeQty, $warehouseId, $staff)
    {
        /* prepare adjuststock data */
        $reason = __('Update from Webpos by staff %1', $staff);
        $adjustData = [];
        $adjustQty = $stockItem->getData('total_qty') + $changeQty;
        $adjustData['products'] = [
            $stockItem->getProductId() => [
                'adjust_qty' => $adjustQty,
                'product_sku' => $stockItem->getSku(),
                'product_name' => $stockItem->getName(),
            ]
        ];
        $adjustData[AdjustStockInterface::WAREHOUSE_ID] = $warehouseId;
        $adjustData[AdjustStockInterface::REASON] = $reason;
        $adjustData[AdjustStockInterface::CREATED_BY] = $staff;
        $adjustData[AdjustStockInterface::CONFIRMED_BY] = $staff;

        /* create new stock adjustment (also update catalog qty), then complete it */
        $adjustStock = $this->adjustStockFactory->create();
        $this->adjustStockManagement->createAdjustment($adjustStock, $adjustData);
        $this->adjustStockManagement->complete($adjustStock, true);
    }
}