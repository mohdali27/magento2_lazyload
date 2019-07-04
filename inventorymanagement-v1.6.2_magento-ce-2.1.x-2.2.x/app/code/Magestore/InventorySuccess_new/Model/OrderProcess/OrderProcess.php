<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\OrderProcess;
use Magestore\InventorySuccess\Helper\Data as InventoryHelper;

class OrderProcess
{
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;
    
    /**
     * @var \Magestore\InventorySuccess\Model\WarehouseFactory
     */
    protected $warehouseFactory;
    
    /**
     * @var \Magestore\InventorySuccess\Model\Warehouse\ProductFactory
     */
    protected $warehouseProductFactory;    
    
    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface
     */
    protected $warehouseManagement;
    
    /**
     * @var type @var \Magestore\InventorySuccess\Model\Warehouse\Sales\ItemFactory
     */
    protected $warehouseOrderItemFactory;
    
    /**
     * @var \Magestore\InventorySuccess\Model\Warehouse\Shipment\ItemFactory
     */
    protected $warehouseShipmentItemFactory;
    
    /**
     * @var \Magestore\InventorySuccess\Model\Warehouse\Creditmemo\ItemFactory
     */
    protected $warehouseCreditmemoItemFactory;  
    
    /**
     * @var \Magestore\InventorySuccess\Model\AdjustStockFactory 
     */
    protected $adjustStockFactory;    
    
    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */
    protected $registry;   
    
    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface 
     */
    protected $warehouseStockRegistry;
    
    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\OrderItemManagementInterface 
     */
    protected $orderItemManagement;    
    
    /**
     * @var \Magento\Framework\App\RequestInterface 
     */
    protected $request;    
    
    /**
     * @var \Magestore\InventorySuccess\Api\Db\QueryProcessorInterface 
     */
    protected $queryProcess;
    
    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $catalogInventoryConfiguration; 
    
    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface
     */
    protected $stockRegistryProvider;
    
    /**
     * @var \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface
     */
    protected $adjustStockManagement;   
    
    /**
     * @var \Magestore\InventorySuccess\Api\Logger\LoggerInterface
     */
    protected $logger;   
    
    /**
     * @var \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface
     */
    protected $stockChange;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var InventoryHelper 
     */
    protected $inventoryHelper;
    
    
    /**
     * @var string
     */
    protected $process = 'order_process';
    
    /**
     * @var array
     */
    protected $orderWarehouseIds = [];
    
    /**
     * @var array
     */
    protected $shipWarehouseIds = [];    

    /**
     * @var array
     */
    protected $returnWarehouseIds = [];
    
    /**
     * @var array
     */
    protected $canceledQtys = [];
   

    /**
     * 
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory
     * @param \Magestore\InventorySuccess\Model\Warehouse\ProductFactory $warehouseProductFactory
     * @param \Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface $warehouseManagement
     * @param \Magestore\InventorySuccess\Model\Warehouse\Order\ItemFactory $warehouseOrderItemFactory
     * @param \Magestore\InventorySuccess\Model\Warehouse\Shipment\ItemFactory $warehouseShipmentItemFactory
     * @param \Magestore\InventorySuccess\Model\Warehouse\Creditmemo\ItemFactory $warehouseCreditmemoItemFactory
     * @param \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegistry
     * @param \Magestore\InventorySuccess\Api\Warehouse\OrderItemManagementInterface $orderItemManagement
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magestore\InventorySuccess\Api\Db\QueryProcessorInterface $queryProcess
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $catalogInventoryConfiguration
     * @param \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface $stockRegistryProvider
     * @param \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface $adjustStockManagement
     * @param \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory
     * @param \Magestore\InventorySuccess\Api\Logger\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\Event\ManagerInterface $eventManager,    
        \Magento\Framework\Registry $registry,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,   
        \Magestore\InventorySuccess\Model\Warehouse\ProductFactory $warehouseProductFactory,
        \Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface $warehouseManagement,
        \Magestore\InventorySuccess\Model\Warehouse\Order\ItemFactory $warehouseOrderItemFactory,
        \Magestore\InventorySuccess\Model\Warehouse\Shipment\ItemFactory $warehouseShipmentItemFactory,
        \Magestore\InventorySuccess\Model\Warehouse\Creditmemo\ItemFactory $warehouseCreditmemoItemFactory,
        \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegistry,
        \Magestore\InventorySuccess\Api\Warehouse\OrderItemManagementInterface $orderItemManagement,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\InventorySuccess\Api\Db\QueryProcessorInterface $queryProcess,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $catalogInventoryConfiguration,
        \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface $stockRegistryProvider,
        \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface $adjustStockManagement,
        \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory,
        \Magestore\InventorySuccess\Api\Logger\LoggerInterface $logger,
        \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface $stockChange,
        InventoryHelper $inventoryHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    )
    {
        $this->eventManager = $eventManager;
        $this->registry = $registry;
        $this->warehouseFactory = $warehouseFactory;
        $this->warehouseProductFactory = $warehouseProductFactory;
        $this->warehouseManagement = $warehouseManagement;
        $this->warehouseOrderItemFactory = $warehouseOrderItemFactory;
        $this->warehouseShipmentItemFactory = $warehouseShipmentItemFactory;
        $this->warehouseCreditmemoItemFactory = $warehouseCreditmemoItemFactory;
        $this->warehouseStockRegistry = $warehouseStockRegistry;
        $this->orderItemManagement = $orderItemManagement;        
        $this->request = $request;
        $this->queryProcess = $queryProcess;
        $this->catalogInventoryConfiguration = $catalogInventoryConfiguration;
        $this->stockRegistryProvider = $stockRegistryProvider;
        $this->adjustStockManagement = $adjustStockManagement;
        $this->adjustStockFactory = $adjustStockFactory;
        $this->logger = $logger;
        $this->stockChange = $stockChange;
        $this->inventoryHelper = $inventoryHelper;
        $this->productRepository = $productRepository;
    }
    
    /**
     * Get orderred warehouse id from orderItemId
     * 
     * @param int $orderItemId
     * @return int
     */
    public function getOrderWarehouse($orderItemId)
    {
        if(!isset($this->orderWarehouseIds[$orderItemId])) {
            $this->orderWarehouseIds[$orderItemId] = $this->orderItemManagement->getWarehouseByItemId($orderItemId);
        }
        return $this->orderWarehouseIds[$orderItemId];
    }
    
    /**
     * Get shipped warehouse id from orderItemId
     * @param int $orderItemId
     * @return int
     */
    public function getShipWarehouse($orderItemId)
    {
        if(!isset($this->shipWarehouseIds[$orderItemId])) {
            $warehouseItem = $this->warehouseShipmentItemFactory->create()
                        ->addFieldToFilter('order_item_id', $orderItemId)
                        ->setPageSize(1)->setCurPage(1)
                        ->getFirstItem(); 
            $this->shipWarehouseIds[$orderItemId] = $warehouseItem->getWarehouseId();         
        }
        return $this->shipWarehouseIds[$orderItemId];
    }
    
    /**
     * Get returned warehouse id from orderItemId
     * @param int $orderItemId
     * @return int
     */
    public function getReturnWarehouse($orderItemId)
    {
        if(!isset($this->returnWarehouseIds[$orderItemId])) {        
            $warehouseItem = $this->warehouseCreditmemoItemFactory->create()
                        ->addFieldToFilter('order_item_id', $orderItemId)
                        ->setPageSize(1)->setCurPage(1)
                        ->getFirstItem();
            $this->returnWarehouseIds[$orderItemId] = $warehouseItem->getWarehouseId();         
        }
        return $this->returnWarehouseIds[$orderItemId];
    }    
    
    /**
     * Mark item processed
     * 
     * @param \Magento\Sales\Model\AbstractModel $item
     */
    public function markItemProcessed($item)
    {
        $key = $this->process.'item'.$item->getId();
        if(!$this->registry->registry($key)){
            $this->registry->register($key, true);
        }
    }
    
    /**
     * Check item processed or not
     * 
     * @param \Magento\Sales\Model\AbstractModel $item
     * @return boolean
     */
    public function isProcessedItem($item)
    {
        $key = $this->process.'item'.$item->getId();
        if($this->registry->registry($key)){
            return true;
        }        
        return false;
    }
    
    /**
     * Manage stock of product in this item or not
     * 
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     */
    public function isManageStock($item)
    {
        /* do not manage qty of this product type */
        $productType = $item->getProductType();
        /**
         * Mark add
         * khi add sp con cua? grouped trong backend va frontend
         * Magento luu product type la grouped nen khong tru available qty
         */
        if ($productType == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE){
            $productType = \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE;
        }
        if(!$this->catalogInventoryConfiguration->isQty($productType)){
            return false;
        }
        
        $scopeId = $this->catalogInventoryConfiguration->getDefaultScopeId();        
        $stockItem = $this->stockRegistryProvider->getStockItem($item->getProductId(), $scopeId);
        
        /* do not manage stock of this product */
        if(!$stockItem->getUseConfigManageStock() && !$stockItem->getManageStock()) {
            return false;
        }
        if($stockItem->getUseConfigManageStock() && !$this->catalogInventoryConfiguration->getManageStock()) {
            return false;
        }         
        return true;
    }
    
    /**
     * Get simple item from order item
     * 
     * @param \Magento\Sales\Model\Order\Item $item
     * @return \Magento\Sales\Model\Order\Item
     */
    protected function _getSimpleItem($item)
    {
        $simpleItem = $item;
        if($item->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            foreach($item->getChildrenItems() as $childItem) {
                $simpleItem = $childItem;
                break;
            }
        }

        return $simpleItem;
    }    
    
    
    /**
     * Get shipped qty
     * 
     * @param \Magento\Sales\Model\Order\Shipment\Item $item
     * @return float
     */    
    protected function _getShippedQty($item)
    {
        return $item->getQty();
    }    
    
    /**
     * Get orderred qty of item
     * 
     * @param \Magento\Sales\Model\Order\Item $item
     * @return int|float
     */
    protected function _getOrderedQty($item) 
    {
        $qtyOrdered = 0;
        /*
        if($parentItem = $item->getParentItem()) {
           if($parentItem->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
               $qtyOrdered = $parentItem->getQtyOrdered();
           }
        }
         */
        $qtyOrdered = $qtyOrdered ? $qtyOrdered : $item->getQtyOrdered();
        return $qtyOrdered;
    }    
    
    /**
     * Get canceled qty of item
     * 
     * @param \Magento\Sales\Model\Order\Item $item
     * @return int|float
     */
    protected function _getCanceledQty($item) 
    {
        if(!isset($this->canceledQtys[$item->getItemId()])) {
            $qtyCanceled = 0;
            
            /* get qty-to-cancel of configurable product */
            if($parentItem = $item->getParentItem()) {
               if($parentItem->getProductType() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                   $this->canceledQtys[$item->getItemId()] = $parentItem->getQtyCanceled();
                   return $this->canceledQtys[$item->getItemId()];
               }
            }
            
            $children = $item->getChildrenItems();
            //$qtyToCancel = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();
            $qtyToCancel = $item->getQtyToCancel();
            if ($item->getId() && $item->getProductId() && empty($children)) {
                $qtyCanceled = $qtyToCancel;
            }    
            $this->canceledQtys[$item->getItemId()] = $qtyCanceled;
        }
        
        return $this->canceledQtys[$item->getItemId()];
    }      

}
