<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Plugin\CatalogInventory\Model;

use Magento\CatalogInventory\Api\StockRepositoryInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockStatusRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Magento\CatalogInventory\Api\Data\StockStatusInterfaceFactory;
use Magento\CatalogInventory\Api\StockCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockStatusCriteriaInterfaceFactory;

use Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface;
use Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface;

class StockRegistryProvider 
{
    
    /**
     * @var StockRepositoryInterface
     */
    protected $stockRepository;

    /**
     * @var StockInterfaceFactory
     */
    protected $stockFactory;

    /**
     * @var StockItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * @var StockItemInterfaceFactory
     */
    protected $stockItemFactory;

    /**
     * @var StockStatusRepositoryInterface
     */
    protected $stockStatusRepository;

    /**
     * @var StockStatusInterfaceFactory
     */
    protected $stockStatusFactory;

    /**
     * @var StockCriteriaInterfaceFactory
     */
    protected $stockCriteriaFactory;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    protected $stockItemCriteriaFactory;

    /**
     * @var StockStatusCriteriaInterfaceFactory
     */
    protected $stockStatusCriteriaFactory;

    /**
     * @var StockRegistryStorage
     */
    protected $stockRegistryStorage;
    
    /**
     * @var WarehouseStockRegistryInterface 
     */
    protected $warehouseStockRegistry;
    
    /**
     * @var WarehouseManagementInterface 
     */
    protected $warehouseManagement;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @param StockRepositoryInterface $stockRepository
     * @param StockInterfaceFactory $stockFactory
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockItemInterfaceFactory $stockItemFactory
     * @param StockStatusRepositoryInterface $stockStatusRepository
     * @param StockStatusInterfaceFactory $stockStatusFactory
     * @param StockCriteriaInterfaceFactory $stockCriteriaFactory
     * @param StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
     * @param StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        StockRepositoryInterface $stockRepository,
        StockInterfaceFactory $stockFactory,
        StockItemRepositoryInterface $stockItemRepository,
        StockItemInterfaceFactory $stockItemFactory,
        StockStatusRepositoryInterface $stockStatusRepository,
        StockStatusInterfaceFactory $stockStatusFactory,
        StockCriteriaInterfaceFactory $stockCriteriaFactory,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        StockStatusCriteriaInterfaceFactory $stockStatusCriteriaFactory,
        WarehouseStockRegistryInterface $warehouseStockRegistry,
        WarehouseManagementInterface $warehouseManagement,
        \Magento\Framework\Registry $registry
    ) {
        $this->stockRepository = $stockRepository;
        $this->stockFactory = $stockFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockItemFactory = $stockItemFactory;
        $this->stockStatusRepository = $stockStatusRepository;
        $this->stockStatusFactory = $stockStatusFactory;
        $this->stockCriteriaFactory = $stockCriteriaFactory;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->stockStatusCriteriaFactory = $stockStatusCriteriaFactory;
        $this->warehouseStockRegistry = $warehouseStockRegistry;
        $this->warehouseManagement = $warehouseManagement;
        $this->registry = $registry;
    }    
    
    /**
     * 
     * @param \Magento\CatalogInventory\Model\StockRegistryProvider $stockRegistryProvider
     * @param int $productId
     * @param int $scopeId
     * @return array
     */
    public function beforeGetStockItem(\Magento\CatalogInventory\Model\StockRegistryProvider $stockRegistryProvider, $productId, $scopeId)
    {
        $webposGetProductList = $this->registry->registry('webpos_get_product_list');
        
        if(!$this->warehouseManagement->isGetStockFromWarehouse() && !$webposGetProductList) {
            return [$productId, $scopeId];
        }
        $stockRegistryStorage = $this->getStockRegistryStorage();
        $stockItem = $stockRegistryStorage->getStockItem($productId, $scopeId);
        if (null === $stockItem) {
            $criteria = $this->stockItemCriteriaFactory->create();
            $criteria->setProductsFilter($productId);
            $criteria->setScopeFilter($scopeId);
            $collection = $this->stockItemRepository->getList($criteria);
            $stockItem = current($collection->getItems());
            if ($stockItem && $stockItem->getItemId()) {
                $stockRegistryStorage->setStockItem($productId, $scopeId, $stockItem);
            }
        }        

        return [$productId, $scopeId];
    }   
     
    
    /**
     * 
     * @return \Magento\CatalogInventory\Model\StockRegistryStorage
     */
    public function getStockRegistryStorage()
    {
        return \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\CatalogInventory\Model\StockRegistryStorage');  
    }
}