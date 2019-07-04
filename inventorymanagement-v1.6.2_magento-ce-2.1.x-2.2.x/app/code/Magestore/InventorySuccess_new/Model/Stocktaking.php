<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model;

use Magento\Framework\Model\AbstractModel;
use Magestore\InventorySuccess\Api\StockActivity\StockActivityInterface;
use Magestore\InventorySuccess\Api\Data\Stocktaking\StocktakingInterface;

/**
 * Class Stocktaking
 * @package Magestore\InventorySuccess\Model
 */
class Stocktaking extends AbstractModel implements StocktakingInterface, StockActivityInterface
{
    /**
     * @var \Magestore\InventorySuccess\Model\Stocktaking\ProductFactory
     */
    protected $stocktakingProductFactory;

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->_init('Magestore\InventorySuccess\Model\ResourceModel\Stocktaking');
    }

    /**
     * 
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magestore\InventorySuccess\Model\Stocktaking\ProductFactory $stocktakingProductFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magestore\InventorySuccess\Model\Stocktaking\ProductFactory $stocktakingProductFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ){
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->stocktakingProductFactory = $stocktakingProductFactory;
    }
    

    /**
     *
     * @return StockActivityInterface
     */
    public function getStockActivityProductModel() {
        return $this->stocktakingProductFactory->create();
    }

    /**
     *
     * @return string
     */
    public function getStocktakingCode()
    {
        return $this->getData(self::STOCKTAKING_CODE);
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
     * @return string
     */
    public function getVerifiedBy()
    {
        return $this->getData(self::VERIFIED_BY);
    }

    /**
     * @return string
     */
    public function getVerifiedAt()
    {
        return $this->getData(self::VERIFIED_AT);
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
     * Get Participants
     *
     * @return string|null
     */
    public function getParticipants()
    {
        return $this->getData(self::PARTICIPANTS);
    }

    /**
     *
     * @return string
     */
    public function getStocktakeAt()
    {
        return $this->getData(self::STOCKTAKE_AT);
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
     * @param string $stocktakingCode
     * @return StocktakingInterface
     */
    public function setStocktakingCode($stocktakingCode)
    {
        return $this->setData(self::STOCKTAKING_CODE, $stocktakingCode);
    }

    /**
     *
     * @param string $confirmedAt
     * @return StocktakingInterface
     */
    public function setConfirmedAt($confirmedAt)
    {
        return $this->setData(self::CONFIRMED_AT, $confirmedAt);
    }

    /**
     *
     * @param string $confirmedBy
     * @return StocktakingInterface
     */
    public function setConfirmedBy($confirmedBy)
    {
        return $this->setData(self::CONFIRMED_BY, $confirmedBy);
    }

    /**
     *
     * @param string $createdAt
     * @return StocktakingInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     *
     * @param string $createdBy
     * @return StocktakingInterface
     */
    public function setCreatedBy($createdBy)
    {
        return $this->setData(self::CREATED_BY, $createdBy);
    }

    /**
     *
     * @param string $verifiedBy
     * @return StocktakingInterface
     */
    public function setVerifiedBy($verifiedBy)
    {
        return $this->setData(self::VERIFIED_BY, $verifiedBy);
    }

    /**
     *
     * @param string $verifiedat
     * @return StocktakingInterface
     */
    public function setVerifiedAt($verifiedAt)
    {
        return $this->setData(self::VERIFIED_AT, $verifiedAt);
    }

    /**
     *
     * @param string $participants
     * @return StocktakingInterface
     */
    public function setParticipants($participants)
    {
        return $this->setData(self::PARTICIPANTS, $participants);
    }

    /**
     *
     * @param string $stocktakeAt
     * @return StocktakingInterface
     */
    public function setStocktakeAt($stocktakeAt)
    {
        return $this->setData(self::STOCKTAKE_AT, $stocktakeAt);
    }
    /**
     *
     * @param string $reason
     * @return StocktakingInterface
     */
    public function setReason($reason)
    {
        return $this->setData(self::REASON, $reason);
    }

    /**
     *
     * @param int $status
     * @return StocktakingInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     *
     * @param int $warehouseId
     * @return StocktakingInterface
     */
    public function setWarehouseId($warehouseId)
    {
        return $this->setData(self::WAREHOUSE_ID, $warehouseId);
    }

    /**
     *
     * @param string $warehouseName
     * @return StocktakingInterface
     */
    public function setWarehouseName($warehouseName)
    {
        return $this->setData(self::WAREHOUSE_NAME, $warehouseName);
    }

    /**
     *
     * @param string $warehouseCode
     * @return StocktakingInterface
     */
    public function setWarehouseCode($warehouseCode)
    {
        return $this->setData(self::WAREHOUSE_CODE, $warehouseCode);
    }

}
