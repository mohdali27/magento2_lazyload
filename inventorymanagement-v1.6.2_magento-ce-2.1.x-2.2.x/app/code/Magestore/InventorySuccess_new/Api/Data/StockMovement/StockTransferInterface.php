<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Data\StockMovement;

interface StockTransferInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const STOCK_TRANSFER_ID = 'stock_transfer_id';
    
    const TRANSFER_CODE = 'transfer_code';

    const QTY = 'qty';
    
    const TOTAL_SKU = 'total_sku';

    const ACTION_CODE = 'action_code';

    const ACTION_ID = 'action_id';

    const ACTION_NUMBER = 'action_number';
    
    const WAREHOUSE_ID = 'warehouse_id';

    const CREATED_AT = 'created_at';

    /**#@-*/
    
    const PREFIX_CODE = 'STR';

    /**
     * Stock movement id
     *
     * @return int|null
     */
    public function getStockTransferId();

    /**
     * Set warehouse id
     *
     * @param int|null $stockMovementId
     * @return $this
     */
    public function setStockTransferId($stockMovementId);
    
    /**
     * get transfer code
     *
     * @return string
     */
    public function getTransferCode();

    /**
     * Set transfer code
     *
     * @param string $code
     * @return $this
     */
    public function setTransferCode($code);    

    /**
     * Product qty
     *
     * @return float
     */
    public function getQty();

    /**
     * Set product qty
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);
    
    /**
     * get total sku
     *
     * @return int
     */
    public function getTotalSku();

    /**
     * Set total sku
     *
     * @param int $totalSku
     * @return $this
     */
    public function setTotalSku($totalSku);    
    
    /**
     * Action code
     *
     * @return string
     */
    public function getActionCode();

    /**
     * Set action code
     *
     * @param string $actionCode
     * @return $this
     */
    public function setActionCode($actionCode);

    /**
     * Action ID
     *
     * @return int
     */
    public function getActionId();

    /**
     * Set action id
     *
     * @param int $actionId
     * @return $this
     */
    public function setActionId($actionId);
    
    /**
     * Reference number
     *
     * @return string|null
     */
    public function getActionNumber();

    /**
     * Set reference number
     *
     * @param string|null $actionNumber
     * @return $this
     */
    public function setActionNumber($actionNumber);

    /**
     * Warehouse id
     *
     * @return int|null
     */
    public function getWarehouseId();

    /**
     * Set warehouse id
     *
     * @param int|null $warehouseId
     * @return $this
     */
    public function setWarehouseId($warehouseId);    
    
    /**
     * Created at
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created at
     *
     * @param string|null $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);
}