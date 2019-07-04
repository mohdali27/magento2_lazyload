<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Warehouse;

use Magestore\InventorySuccess\Api\StockActivity\StockActivityProductInterface;
use Magestore\InventorySuccess\Api\Data\AdjustStock\AdjustStockInterface;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\CollectionFactory as WarehouseProductCollectionFactory;

/**
 * Class Product
 * @package Magestore\InventorySuccess\Model\Warehouse
 */
class Product extends \Magento\Framework\Model\AbstractModel implements StockActivityProductInterface, WarehouseProductInterface
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'inventorysuccess_warehouse_product';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getWarehouseProduct() in this case
     *
     * @var string
     */
    protected $_eventObject = 'warehouse_product';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface
     */
    protected $warehouseStockRegistry;

    /**
     * @var \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface
     */
    protected $_adjustStockManagement;

    /**
     * @var WarehouseProductCollectionFactory
     */
    protected $warehouseProductCollectionFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\AdjustStockFactory
     */
    protected $_adjustStockFactory; 
    
    /**
     * @var ProductFactory
     */
    protected $productFactory;

    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegistry,
        \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface $adjustStockManagement,
        WarehouseProductCollectionFactory $warehouseProductCollectionFactory,
        \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_objectManager = $objectManager;
        $this->warehouseStockRegistry = $warehouseStockRegistry;
        $this->_adjustStockManagement = $adjustStockManagement;
        $this->warehouseProductCollectionFactory = $warehouseProductCollectionFactory;
        $this->_adjustStockFactory = $adjustStockFactory;
        $this->productFactory = $productFactory;
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product');
    }

    /**
     * update stock in grid stock on hand
     *
     * @param array $stockData
     * @param int $warehouseId
     * @return $this
     */
    public function updateStockInGrid($warehouseId = null, $stockData = []){
        if(!$warehouseId)
            return $this;
        $adjustData = [];
        foreach ($stockData as $productId => $value){
            $stockItemData = json_decode($value, true);
            if(isset($stockItemData['total_qty']) && isset($stockItemData['old_qty']) && 
                $stockItemData['total_qty']!=$stockItemData['old_qty']){
                $product = $this->productFactory->create()->load($productId);
                $adjustData['products'][$productId] = [
                    'adjust_qty' => $stockItemData['total_qty'],
                    'old_qty' => $stockItemData['old_qty'],
                    'product_sku' => $product->getSku(),
                    'product_name' => $product->getName()
                ];
            }
            $this->_updateShelfLocation($warehouseId, $productId, $stockItemData);
        }
        if(isset($adjustData['products']) && count($adjustData['products'])>0){
            $adjustData = $this->_prepareAdjustmentData($adjustData, $warehouseId);
            $this->createAdjustment($adjustData, true);
        }
        return $this;
    }

    /**
     * Prepare stock adjustment data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $data
     * @return array
     */
    protected function _prepareAdjustmentData($adjustData, $warehouseId)
    {
        $adjustData[AdjustStockInterface::WAREHOUSE_ID] = $warehouseId;
        $adjustData[AdjustStockInterface::WAREHOUSE_NAME] = null;
        $adjustData[AdjustStockInterface::WAREHOUSE_CODE] = null;
        $adjustData[AdjustStockInterface::REASON] = __('Update Stock In Grid');
        return $adjustData;
    }

    /**
     * Create stock adjustment
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $adjustData
     * @return \Magestore\InventorySuccess\Model\AdjustStock
     */
    public function createAdjustment($adjustData, $updateCatalog = true)
    {
        $adjustStock = $this->_adjustStockFactory->create();
        /* create stock adjustment, require products */
        $this->_adjustStockManagement->createAdjustment($adjustStock, $adjustData, true);
        /* created adjuststock or not */
        if($adjustStock->getId()) {
            /* complete stock adjustment */
            $this->_adjustStockManagement->complete($adjustStock, $updateCatalog);
        }
        return $adjustStock;
    }
    
    /**
     * @param int $warehouseId
     * @param int $productId
     * @param int $stockItemData
     * @return $this
     */
    protected function _updateShelfLocation($warehouseId, $productId, $stockItemData)
    {
        $shelfLocation = isset($stockItemData['shelf_location']) ? $stockItemData['shelf_location'] : null;
        $this->warehouseStockRegistry->updateLocation($warehouseId, [$productId => $shelfLocation]);
        return $this;
    }
    
    /**
     * @return int|null
     */
    public function getWarehouseProductId()
    {
        return $this->_getData(self::WAREHOUSE_PRODUCT_ID);
    }

    /**
     * @return int|null
     */
    public function getWarehouseId()
    {
        return $this->_getData(self::WAREHOUSE_ID);
    }

    /**
     * @param int $warehouseId
     * @return $this
     */
    public function setWarehouseId($warehouseId)
    {
        return $this->setData(self::WAREHOUSE_ID, $warehouseId);
    }

    /**
     * @return int|null
     */
    public function getProductId()
    {
        return $this->_getData(self::PRODUCT_ID);
    }

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @return float
     */
    public function getTotalQty()
    {
        return $this->_getData(self::TOTAL_QTY);
    }

    /**
     * @param float $totalQty
     * @return $this
     */
    public function setTotalQty($totalQty)
    {
        return $this->setData(self::TOTAL_QTY, $totalQty);
    }
    
    /**
     * @return float
     */
    public function getAvailableQty()
    {
        return $this->_getData(self::AVAILABLE_QTY);
    }

    /**
     * @param float $qty
     * @return $this
     */
    public function setAvailableQty($qty)
    {
        return $this->setData(self::AVAILABLE_QTY, $qty);
    }

    /**
     * @return float
     */
    public function getQtyToShip()
    {
        return max(0, $this->getTotalQty() - $this->getAvailableQty());
    }

    /**
     * @param float $qtyToShip
     * @return $this
     */
    public function setQtyToShip($qtyToShip)
    {
        return $this;
    }

    /**
     * @return null|string
     */
    public function getShelfLocation()
    {
        return $this->_getData(self::SHELF_LOCATION);
    }

    /**
     * @param string $shelfLocation
     * @return $this
     */
    public function setShelfLocation($shelfLocation)
    {
        return $this->setData(self::SHELF_LOCATION, $shelfLocation);
    }

    /**
     * Created at
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_getData(self::CREATED_AT);
    }

    /**
     * Set created at
     *
     * @param int $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Updated at
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_getData(self::UPDATED_AT);
    }

    /**
     * Set updated at
     *
     * @param int $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    public function getCustomAttribute($attributeCode)
    {
        
    }

    public function getCustomAttributes()
    {
        
    }

    public function setCustomAttribute($attributeCode, $attributeValue)
    {
        
    }

    public function setCustomAttributes(array $attributes)
    {
        
    }

}