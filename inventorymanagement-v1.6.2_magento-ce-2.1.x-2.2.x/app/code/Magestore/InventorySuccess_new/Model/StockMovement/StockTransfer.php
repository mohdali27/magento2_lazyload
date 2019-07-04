<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\StockMovement;

use Magento\Framework\Model\AbstractModel as AbstractModel;
use Magestore\InventorySuccess\Api\Data\StockMovement\StockTransferInterface;


class StockTransfer extends AbstractModel implements StockTransferInterface
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->_init('Magestore\InventorySuccess\Model\ResourceModel\StockMovement\StockTransfer');
    }
    
    /**
     * Stock movement id
     *
     * @return int|null
     */
    public function getStockTransferId()
    {
        return $this->_getData(self::STOCK_TRANSFER_ID);
    }

    /**
     * Set id
     *
     * @param int|null $stockTransferId
     * @return $this
     */
    public function setStockTransferId($stockTransferId)
    {
        return $this->setData(self::STOCK_TRANSFER_ID, $stockTransferId);
    }
    
    /**
     * get transfer code
     *
     * @return string
     */
    public function getTransferCode()
    {
        return $this->_getData(self::TRANSFER_CODE);
    }

    /**
     * Set transfer code
     *
     * @param string $code
     * @return $this
     */
    public function setTransferCode($code)
    {
        return $this->setData(self::TRANSFER_CODE, $code);
    }

    /**
     * Product qty
     *
     * @return float
     */
    public function getQty()
    {
        return $this->_getData(self::QTY);
    }

    /**
     * Set product qty
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

    /**
     * get total sku
     *
     * @return int
     */
    public function getTotalSku()
    {
        return $this->_getData(self::TOTAL_SKU);
    }

    /**
     * Set total sku
     *
     * @param int $totalSku
     * @return $this
     */
    public function setTotalSku($totalSku)
    {
        return $this->setData(self::TOTAL_SKU, $totalSku);
    }

    /**
     * Action code
     *
     * @return string
     */
    public function getActionCode()
    {
        return $this->_getData(self::ACTION_CODE);
    }

    /**
     * Set action code
     *
     * @param string $actionCode
     * @return $this
     */
    public function setActionCode($actionCode)
    {
        return $this->setData(self::ACTION_CODE, $actionCode);
    }

    /**
     * Action ID
     *
     * @return int
     */
    public function getActionId()
    {
        return $this->_getData(self::ACTION_ID);
    }

    /**
     * Set action id
     *
     * @param int $actionId
     * @return $this
     */
    public function setActionId($actionId)
    {
        return $this->setData(self::ACTION_ID, $actionId);
    }

    /**
     * Reference number
     *
     * @return string|null
     */
    public function getActionNumber()
    {
        return $this->_getData(self::ACTION_NUMBER);
    }

    /**
     * Set action number
     *
     * @param string|null $action
     * @return $this
     */
    public function setActionNumber($actionNumber)
    {
        return $this->setData(self::ACTION_NUMBER, $actionNumber);
    }

    /**
     * get warehouse id
     *
     * @return int|null
     */
    public function getWarehouseId()
    {
        return $this->_getData(self::WAREHOUSE_ID);
    }

    /**
     * Set warehouse id
     *
     * @param int|null $warehouseId
     * @return $this
     */
    public function setWarehouseId($warehouseId)
    {
        return $this->setData(self::WAREHOUSE_ID, $warehouseId);
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
     * @param string|null $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

}