<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Data\AdjustStock;

use Magento\Framework\Model\AbstractModel;
use Magestore\InventorySuccess\Api\Data\AdjustStock\CreateAdjustStockRequestInterface;

class CreateAdjustStockRequest extends AbstractModel implements CreateAdjustStockRequestInterface
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
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magestore\InventorySuccess\Model\ResourceModel\AdjustStock');
    }

    /**
     * AdjustStock constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \AdjustStock\ProductFactory $adjustStockProductFactory
     * @param \ResourceModel\AdjustStock\Product\CollectionFactory $adjuststockProductCollectionFactory
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
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_adjuststockProductFactory = $adjustStockProductFactory;
        $this->adjuststockProductCollectionFactory = $adjuststockProductCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function getAdjustStockId()
    {
        return $this->getData(self::ADJUSTSTOCK_ID);
    }

    /**
     * @inheritdoc
     */
    public function setAdjustStockId($adjustStockId)
    {
        return $this->setData(self::ADJUSTSTOCK_ID, $adjustStockId);
    }

    /**
     * @inheritdoc
     */
    public function getAdjustStockCode()
    {
        return $this->getData(self::ADJUSTSTOCK_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setAdjustStockCode($adjustStockCode)
    {
        return $this->setData(self::ADJUSTSTOCK_CODE, $adjustStockCode);
    }

    /**
     * @inheritdoc
     */
    public function getWarehouseId()
    {
        return $this->getData(self::WAREHOUSE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setWarehouseId($warehouseId)
    {
        return $this->setData(self::WAREHOUSE_ID, $warehouseId);
    }

    /**
     * @inheritdoc
     */
    public function getWarehouseCode()
    {
        return $this->getData(self::WAREHOUSE_CODE);
    }

    /**
     * @inheritdoc
     */
    public function setWarehouseCode($warehouseCode)
    {
        return $this->setData(self::WAREHOUSE_CODE, $warehouseCode);
    }

    /**
     * @inheritdoc
     */
    public function getWarehouseName()
    {
        return $this->getData(self::WAREHOUSE_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setWarehouseName($warehouseName)
    {
        return $this->setData(self::WAREHOUSE_NAME, $warehouseName);
    }


    /**
     * @inheritdoc
     */
    public function getConfirmedAt()
    {
        return $this->getData(self::CONFIRMED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setConfirmedAt($confirmedAt)
    {
        return $this->setData(self::CONFIRMED_AT, $confirmedAt);
    }

    /**
     * @inheritdoc
     */
    public function getConfirmedBy()
    {
        return $this->getData(self::CONFIRMED_BY);
    }

    /**
     * @inheritdoc
     */
    public function setConfirmedBy($confirmedBy)
    {
        return $this->setData(self::CONFIRMED_BY, $confirmedBy);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedBy()
    {
        return $this->getData(self::CREATED_BY);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedBy($createdBy)
    {
        return $this->setData(self::CREATED_BY, $createdBy);
    }

    /**
     * @inheritdoc
     */
    public function getReason()
    {
        return $this->getData(self::REASON);
    }

    /**
     * @inheritdoc
     */
    public function setReason($reason)
    {
        return $this->setData(self::REASON, $reason);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritDoc
     */
    public function getAction()
    {
        return $this->getData(self::ACTION);
    }

    /**
     * @inheritDoc
     */
    public function setAction($action)
    {
        return $this->setData(self::ACTION, $action);
    }

    /**
     * @inheritDoc
     */
    public function getProducts()
    {
        return $this->getData(self::KEY_PRODUCTS);
    }

    /**
     * @inheritDoc
     */
    public function setProducts(array $products = null)
    {
        return $this->setData(self::KEY_PRODUCTS, $products);
    }
}
