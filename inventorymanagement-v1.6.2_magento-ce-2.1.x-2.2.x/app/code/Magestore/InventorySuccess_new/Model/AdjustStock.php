<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model;

use Magento\Framework\Model\AbstractModel;
use Magestore\InventorySuccess\Api\StockActivity\StockActivityInterface;
use Magestore\InventorySuccess\Api\Data\AdjustStock\AdjustStockInterface;

/**
 * Class AdjustStock
 * @package Magestore\InventorySuccess\Model
 */
class AdjustStock extends AbstractModel implements AdjustStockInterface, StockActivityInterface
{
    /**
     * @var \Magestore\InventorySuccess\Model\AdjustStock\ProductFactory
     */
    protected $_adjuststockProductFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\AdjustStock\Product\CollectionFactory
     */
    protected $adjuststockProductCollectionFactory;

    /**
     * @var \Magestore\InventorySuccess\Api\Logger\LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->_init('Magestore\InventorySuccess\Model\ResourceModel\AdjustStock');
    }

    /**
     * AdjustStock constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param AdjustStock\ProductFactory $adjustStockProductFactory
     * @param ResourceModel\AdjustStock\Product\CollectionFactory $adjuststockProductCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magestore\InventorySuccess\Model\AdjustStock\ProductFactory $adjustStockProductFactory,
        \Magestore\InventorySuccess\Model\ResourceModel\AdjustStock\Product\CollectionFactory $adjuststockProductCollectionFactory,
        \Magestore\InventorySuccess\Api\Logger\LoggerInterface $logger,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ){
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_adjuststockProductFactory = $adjustStockProductFactory;
        $this->adjuststockProductCollectionFactory = $adjuststockProductCollectionFactory;
        $this->logger = $logger;
    }
    

    /**
     * 
     * @return StockActivityInterface
     */
    public function getStockActivityProductModel() {
        return $this->_adjuststockProductFactory->create();
    }

    public function getAdjustStockId()
    {
        return $this->getData(self::ADJUSTSTOCK_ID);
    }

    /**
     * 
     * @return string
     */
    public function getAdjustStockCode()
    {
        return $this->getData(self::ADJUSTSTOCK_CODE);
    }
    
    /**
     * 
     * @return string
     */
    public function getConfirmedAt()
    {
        return $this->getData(self::CONFIRMED_AT);
    }
    
    /**
     * 
     * @return string
     */
    public function getConfirmedBy()
    {
        return $this->getData(self::CONFIRMED_BY);
    }

    /**
     * 
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * 
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->getData(self::CREATED_BY);
    }

    /**
     * 
     * @return string
     */
    public function getReason()
    {
        return $this->getData(self::REASON);
    }

    /**
     * 
     * @return int
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * 
     * @return int
     */
    public function getWarehouseId()
    {
        return $this->getData(self::WAREHOUSE_ID);
    }

    /**
     * 
     * @return string
     */
    public function getWarehouseCode()
    {
        return $this->getData(self::WAREHOUSE_CODE);
    }    

    /**
     * 
     * @return string
     */
    public function getWarehouseName()
    {
        return $this->getData(self::WAREHOUSE_NAME);
    }
    
    /**
     * 
     * @param string $adjustStockCode
     * @return AdjustStockInterface
     */
    public function setAdjustStockCode($adjustStockCode)
    {
        return $this->setData(self::ADJUSTSTOCK_CODE, $adjustStockCode);
    }

    /**
     * 
     * @param string $confirmedAt
     * @return AdjustStockInterface
     */
    public function setConfirmedAt($confirmedAt)
    {
        return $this->setData(self::CONFIRMED_AT, $confirmedAt);
    }

    /**
     * 
     * @param string $confirmedBy
     * @return AdjustStockInterface
     */
    public function setConfirmedBy($confirmedBy)
    {
        return $this->setData(self::CONFIRMED_BY, $confirmedBy);
    }

    /**
     * 
     * @param string $createdAt
     * @return AdjustStockInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * 
     * @param string $createdBy
     * @return AdjustStockInterface
     */
    public function setCreatedBy($createdBy)
    {
        return $this->setData(self::CREATED_BY, $createdBy);
    }

    /**
     * 
     * @param string $reason
     * @return AdjustStockInterface
     */
    public function setReason($reason)
    {
        return $this->setData(self::REASON, $reason);
    }

    /**
     * 
     * @param int $status
     * @return AdjustStockInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * 
     * @param int $warehouseId
     * @return AdjustStockInterface
     */
    public function setWarehouseId($warehouseId)
    {
        return $this->setData(self::WAREHOUSE_ID, $warehouseId);
    }

    /**
     * 
     * @param string $warehouseName
     * @return int 
     */
    public function setWarehouseName($warehouseName)
    {
        return $this->setData(self::WAREHOUSE_NAME, $warehouseName);
    }

    /**
     * 
     * @param string $warehouseCode
     * @return AdjustStockInterface
     */
    public function setWarehouseCode($warehouseCode)
    {
        return $this->setData(self::WAREHOUSE_CODE, $warehouseCode);
    }

    /**
     * @inheritDoc
     */
    public function getProducts()
    {
        $adjustStockId = $this->getId();
        /** @var \Magestore\InventorySuccess\Model\ResourceModel\AdjustStock\Product\Collection $products */
        $products = $this->adjuststockProductCollectionFactory->create()->addFieldToFilter('adjuststock_id', ['eq' => $adjustStockId]);
        return $products->getData();
    }

    /**
     * @inheritDoc
     */
    public function setProducts(array $products = null)
    {
        /** @var \Magestore\InventorySuccess\Api\Data\AdjustStock\ProductInterface $product */
//        foreach ($products as $product) {
//            $existingAdjustStockProduct = $this->getProduct($product->getAdjuststockProductId());
//            try {
//                /** @var \Magestore\InventorySuccess\Model\AdjustStock\Product $newAdjustStockProduct */
//                $newAdjustStockProduct = $this->_adjuststockProductFactory->create();
//                if ($existingAdjustStockProduct->getId()) {
//                    $newAdjustStockProduct->setIdFieldName($existingAdjustStockProduct->getId());
//                }
//                $newAdjustStockProduct
//                    ->setAdjuststockId($this->getId())
//                    ->setProductId($product->getProductId())
//                    ->setProductName($product->getProductName())
//                    ->setProductSku($product->getProductSku())
//                    ->setOldQty($product->getOldQty())
//                    ->setSuggestQty($product->getSuggestQty())
//                    ->setAdjustQty($product->getAdjustQty())
//                    ->save();
//                return $this->getProduct($newAdjustStockProduct->getAdjuststockProductId());
//            } catch (\Exception $e) {
//                $this->logger->log($e->getMessage(), 'apiCreateAdjustStockProduct');
//            }
//        }
    }
}
