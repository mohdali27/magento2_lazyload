<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Warehouse;

use Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface;
use Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface;
use Magestore\InventorySuccess\Model\WarehouseFactory;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Stock\CollectionFactory as StockCollectionFactory;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory as WarehouseCollectionFactory;
use Magestore\InventorySuccess\Model\ResourceModel\StockMovement\CollectionFactory as StockMovementCollectionFactory;
use Magestore\InventorySuccess\Model\Warehouse\Options\Status as WarehouseStatus;
use \Magento\Store\Model\StoreManagerInterface;
use Magestore\InventorySuccess\Helper\Data as InventoryHelper;
use Magestore\InventorySuccess\Helper\System as SystemHelper;

/**
 * Class WarehouseManagement
 * @package Magestore\InventorySuccess\Model\Warehouse
 */
class WarehouseManagement implements WarehouseManagementInterface
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magestore\InventorySuccess\Model\WarehouseFactory
     */
    protected $warehouseFactory;

    /**
     * @var StockCollectionFactory
     */
    protected $stockCollectionFactory;

    /**
     * @var StockMovementCollectionFactory
     */
    protected $stockMovementCollectionFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory
     */
    protected $warehouseCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManagement;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var InventoryHelper
     */
    protected $inventoryHelper;

    /**
     * @var SystemHelper
     */
    protected $systemHelper;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\WarehouseStoreViewMap\CollectionFactory
     */
    protected $warehouseStoreViewMapCollectionFactory;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;
    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $sessionQuote;

    /**
     * WarehouseManagement constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param WarehouseFactory $warehouseFactory
     * @param StockCollectionFactory $stockCollectionFactory
     * @param StockMovementCollectionFactory $stockMovementCollectionFactory
     * @param WarehouseCollectionFactory $warehouseCollectionFactory
     * @param StoreManagerInterface $storeManagement
     * @param InventoryHelper $inventoryHelper
     * @param SystemHelper $systemHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magestore\InventorySuccess\Model\ResourceModel\WarehouseStoreViewMap\CollectionFactory $warehouseStoreViewMapCollectionFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        StockCollectionFactory $stockCollectionFactory,
        StockMovementCollectionFactory $stockMovementCollectionFactory,
        WarehouseCollectionFactory $warehouseCollectionFactory,
        StoreManagerInterface $storeManagement,
        InventoryHelper $inventoryHelper,
        SystemHelper $systemHelper,
        \Magento\Framework\Registry $registry,
        \Magestore\InventorySuccess\Model\ResourceModel\WarehouseStoreViewMap\CollectionFactory $warehouseStoreViewMapCollectionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Backend\Model\Session\Quote $sessionQuote
    )
    {
        $this->_objectManager = $objectManager;
        $this->warehouseFactory = $warehouseFactory;
        $this->stockCollectionFactory = $stockCollectionFactory;
        $this->stockMovementCollectionFactory = $stockMovementCollectionFactory;
        $this->warehouseCollectionFactory = $warehouseCollectionFactory;
        $this->storeManagement = $storeManagement;
        $this->inventoryHelper = $inventoryHelper;
        $this->systemHelper = $systemHelper;
        $this->registry = $registry;
        $this->warehouseStoreViewMapCollectionFactory = $warehouseStoreViewMapCollectionFactory;
        $this->eventManager = $eventManager;
        $this->sessionQuote = $sessionQuote;
    }

    /**
     * @inheritdoc
     */
    public function getListProduct($warehouseId)
    {
        $collection = $this->stockCollectionFactory->create();
        if ($warehouseId) {
            $collection->addWarehouseToFilter($warehouseId);
        }
        return $collection;
    }

    /**
     * @inheritdoc
     */
    public function getStockMovement($warehouseId)
    {
        $this->stockMovementCollectionFactory->create()
            ->addFieldToFilter(
                ['source_warehouse_id', 'des_warehouse_id'],
                [
                    ['eq' => $warehouseId],
                    ['eq' => $warehouseId],
                ]
            );
    }

    /**
     * get primary warehouse
     *
     * @return \Magestore\InventorySuccess\Model\Warehouse
     */
    public function getPrimaryWarehouse()
    {
        return $this->warehouseCollectionFactory->create()
            ->addFieldToFilter(WarehouseInterface::IS_PRIMARY, 1)
            ->setPageSize(1)
            ->setCurPage(1)
            ->getFirstItem();
    }

    /**
     * get Warehouses by Ids
     *
     * @param array $ids
     * @return array
     */
    public function getWarehouses($ids)
    {
        $warehouses = [];
        $collection = $this->warehouseCollectionFactory->create()
            ->addFieldToFilter(WarehouseInterface::WAREHOUSE_ID, ['in' => $ids]);
        if ($collection->getSize()) {
            foreach ($collection as $warehouse) {
                $warehouses[$warehouse->getId()] = $warehouse->getData();
            }
        }
        return $warehouses;
    }

    /**
     * get enable warehouses
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection
     */
    public function getEnableWarehouses()
    {
        $collection = $this->warehouseCollectionFactory->create();
//                             ->addFieldToFilter(WarehouseInterface::STATUS, WarehouseStatus::STATUS_ENABLED);
        return $collection;
    }

    /**
     * get disable warehouses
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection
     */
    public function getDisableWarehouses()
    {
        $collection = $this->warehouseCollectionFactory->create()
            ->addFieldToFilter(WarehouseInterface::STATUS, WarehouseStatus::STATUS_DISABLED);
        return $collection;
    }

    /**
     *
     * @return WarehouseInterface
     */
    public function getCurrentWarehouseByStore()
    {
        if($this->systemHelper->isAdminArea()) {
            $storeId = $this->sessionQuote->getStore()->getId();
        } else {
            $storeId = $this->storeManagement->getStore()->getId();
        }
        $warehouse = $this->warehouseFactory->create();
        $warehouseId = $this->warehouseStoreViewMapCollectionFactory->create()
            ->addFieldToFilter('store_id', $storeId)
            ->getFirstItem()
            ->getWarehouseId();
        if ($warehouseId)
            $warehouse->getResource()->load($warehouse, $warehouseId);
        $this->eventManager->dispatch('inventorysuccess_get_current_warehouse_by_store', ['warehouse' => $warehouse]);
        return $warehouse;
    }

    /**
     * @param string $storeId
     * @return WarehouseInterface
     */
    public function getWarehouseByStoreId($storeId)
    {
        if(!$storeId) {
            $storeId = $this->storeManagement->getStore()->getId();
        }
        $warehouse = $this->warehouseFactory->create();
        $warehouseId = $this->warehouseStoreViewMapCollectionFactory->create()
            ->addFieldToFilter('store_id', $storeId)
            ->getFirstItem()
            ->getWarehouseId();
        if ($warehouseId)
            $warehouse->getResource()->load($warehouse, $warehouseId);
        $this->eventManager->dispatch('inventorysuccess_get_current_warehouse_by_store', ['warehouse' => $warehouse]);
        return $warehouse;
    }

    /**
     *
     * @return bool
     */
    public function isGetStockFromWarehouse()
    {
        if (!$this->inventoryHelper->getLinkWarehouseStoreConfig()
            || $this->systemHelper->isAdminArea()
            || $this->registry->registry(self::BEFORE_SUBTRACT_SALES_QTY)
        ) {
            return false;
        }
        return true;
    }

}
