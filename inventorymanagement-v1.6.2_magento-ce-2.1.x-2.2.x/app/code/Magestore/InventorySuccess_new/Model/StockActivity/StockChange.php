<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\StockActivity;

use Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface;

class StockChange implements StockChangeInterface
{
    
    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChangeFactory
     */
    protected $_resourceStockChangeFactory;
    
    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface
     */
    protected $_warehouseStockRegistry;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * StockChange constructor.
     * @param \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChangeFactory $resourceStockChangeFactory
     * @param \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegistry
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChangeFactory $resourceStockChangeFactory,
        \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegistry,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->_resourceStockChangeFactory = $resourceStockChangeFactory;
        $this->_warehouseStockRegistry = $warehouseStockRegistry;
        $this->eventManager = $eventManager;
    }    

    /**
     * @inheritdoc
     */
    public function change($warehouseId, $productId, $qtyChange, $updateCatalog = true)
    {
        $this->getResource()->change($warehouseId, $productId, $qtyChange, $updateCatalog);
        return $this;        
    }

    /**
     * @inheritdoc
     */
    public function decrease($warehouseId, $productId, $qty, $updateCatalog = true)
    {
        $this->getResource()->decrease($warehouseId, $productId, $qty, $updateCatalog);
        return $this;            
    }

    /**
     * @inheritdoc
     */
    public function increase($warehouseId, $productId, $qty, $updateCatalog = true)
    {
        $this->getResource()->increase($warehouseId, $productId, $qty, $updateCatalog);
        return $this;           
    }

    /**
     * @inheritdoc
     */
    public function massChange($warehouseId, $qtys, $updateCatalog = true)
    {
        $this->getResource()->massChange($warehouseId, $qtys, $updateCatalog);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function massUpdate($warehouseId, $qtys, $updateCatalog = true)
    {
        $this->getResource()->massUpdate($warehouseId, $qtys, $updateCatalog);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function update($warehouseId, $productId, $qtyUpdate, $updateCatalog = true)
    {
        $this->getResource()->update($warehouseId, $productId, $qtyUpdate, $updateCatalog);
        return $this;              
    }
    
    /**
     * Get resource model
     * 
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChange
     */
    public function getResource() {
        return $this->_resourceStockChangeFactory->create();
    }   
    
    /**
     * Get stocks from Warehouse
     * 
     * @param int $warehouseId
     * @param int $productIds
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection
     */
    public function getStocks($warehouseId, $productIds = [])
    {
        return $this->_warehouseStockRegistry->getStocks($warehouseId, $productIds);
    }
    
    /**
     * Adjust stocks in the warehouse
     * 
     * @param int $warehouseId
     * @param array $products
     * @param string $actionType
     * @param int $actionId
     * @param bool $updateCatalog
     * @return StockChangeInterface
     */
    public function adjust($warehouseId, $products, $actionType, $actionId, $updateCatalog = true)
    {
        /* format qty before updating */
        $changeQtys = $this->_prepareProductQtys($products, self::ADJUST_STOCK);
        /* update stocks in warehouse & global stocks */
        $this->massUpdate($warehouseId, $changeQtys, $updateCatalog);
        
        $this->eventManager->dispatch('stockchange_adjust_stock_after', [
            'warehouse_id' => $warehouseId,
            'products' => $products,
            'action_type' => $actionType,
            'action_id' => $actionId,
        ]);
        return $this;
    }

    /**
     * Issue stocks from the warehouse
     * 
     * @param int $warehouseId
     * @param array $products
     * @param string $actionType
     * @param int $actionId
     * @param bool $updateCatalog
     * @return StockChangeInterface
     */    
    public function issue($warehouseId, $products, $actionType, $actionId, $updateCatalog = true)
    {
        /* format qty before updating */
        $changeQtys = $this->_prepareProductQtys($products, self::ISSUE_STOCK);
        /* update stocks in warehouse & global stocks */
        $this->massChange($warehouseId, $changeQtys, $updateCatalog);
        
        $this->eventManager->dispatch('stockchange_issue_stock_after', [
            'warehouse_id' => $warehouseId,
            'products' => $changeQtys,
            'action_type' => $actionType,
            'action_id' => $actionId,
        ]);
        return $this;        
    }

    /**
     * Receive stocks to the warehouse
     * 
     * @param int $warehouseId
     * @param array $products
     * @param string $actionType
     * @param int $actionId
     * @param bool $updateCatalog
     * @return StockChangeInterface
     */    
    public function receive($warehouseId, $products, $actionType, $actionId, $updateCatalog = true)
    {
        /* format qty before updating */
        $changeQtys = $this->_prepareProductQtys($products, self::RECEIVE_STOCK);
        /* update stocks in warehouse & global stocks */
        $this->massChange($warehouseId, $changeQtys, $updateCatalog);
        
        $this->eventManager->dispatch('stockchange_receive_stock_after', [
            'warehouse_id' => $warehouseId,
            'products' => $changeQtys,
            'action_type' => $actionType,
            'action_id' => $actionId,
        ]);        
        return $this;         
    }
    
    /**
     * Format product qty
     * 
     * @param array $products
     * @return array 
     */
    protected function _prepareProductQtys($products, $stockAction)
    {
        $prepareProducts = [];
        if(!count($products)) {
            return [];
        }
        
        foreach($products as $productId => $qty) {
            $formatQty = $qty;
            switch ($stockAction) {
                case self::ADJUST_STOCK:
                    $formatQty = floatval($qty['adjust_qty']);
                    break;
                case self::ISSUE_STOCK:
                    $formatQty = -abs(floatval($qty));
                    break;
                case self::RECEIVE_STOCK:
                    $formatQty = abs(floatval($qty));
                    break;
            }
            $prepareProducts[$productId] = $formatQty;
        }
        
        return $prepareProducts;
    }    

}