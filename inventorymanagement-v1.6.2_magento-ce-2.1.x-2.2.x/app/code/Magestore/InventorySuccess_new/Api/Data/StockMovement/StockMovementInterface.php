<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Data\StockMovement;

interface StockMovementInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const STOCK_MOVEMENT_ID = 'stock_movement_id';
    
    const PRODUCT_ID = 'product_id';

    const PRODUCT_SKU = 'product_sku';

    const QTY = 'qty';

    const ACTION_CODE = 'action_code';

    const ACTION_ID = 'action_id';

    const ACTION_NUMBER = 'action_number';
    
    const REFERENCE_NUMBER = 'reference_number';
    
    const WAREHOUSE_ID = 'warehouse_id';

    const SOURCE_WAREHOUSE_ID = 'source_warehouse_id';

    const SOURCE_WAREHOUSE_CODE = 'source_warehouse_code';

    const DES_WAREHOUSE_ID = 'des_warehouse_id';
    
    const DES_WAREHOUSE_CODE = 'des_warehouse_code';

    const EXTERNAL_LOCATION = 'external_location';

    const CREATED_AT = 'created_at';
    
    const STOCK_TRANSFER_ID = 'stock_transfer_id';    

    /**#@-*/

    /**
     * Stock movement id
     *
     * @return int|null
     */
    public function getStockMovementId();

    /**
     * Set warehouse id
     *
     * @param int|null $stockMovementId
     * @return $this
     */
    public function setStockMovementId($stockMovementId);

    /**
     * Product id
     *
     * @return int
     */
    public function getProductId();

    /**
     * Set product id
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * Product SKU
     *
     * @return string|null
     */
    public function getProductSku();

    /**
     * Set product sku
     *
     * @param string|null $productSku
     * @return $this
     */
    public function setProductSku($productSku);

    /**
     * Product qty
     *
     * @return int
     */
    public function getQty();

    /**
     * Set product qty
     *
     * @param int $qty
     * @return $this
     */
    public function setQty($qty);
    
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
     * get Action number
     *
     * @return string
     */
    public function getActionNumber();

    /**
     * Set action number
     *
     * @param string $actionNumber
     * @return $this
     */
    public function setActionNumber($actionNumber);    
    
    /**
     * Reference number
     *
     * @return string|null
     */
    public function getReferenceNumber();

    /**
     * Set reference number
     *
     * @param string|null $referenceNumber
     * @return $this
     */
    public function setReferenceNumber($referenceNumber);

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
     * Source warehouse Id
     *
     * @return int|null
     */
    public function getSourceWarehouseId();

    /**
     * Set source warehouse id
     *
     * @param int|null $sourceWarehouseId
     * @return $this
     */
    public function setSourceWarehouseId($sourceWarehouseId);
    
    /**
     * Source warehouse code
     *
     * @return string|null
     */
    public function getSourceWarehouseCode();

    /**
     * Set source warehouse code
     *
     * @param string|null $sourceWarehouseCode
     * @return $this
     */
    public function setSourceWarehouseCode($sourceWarehouseCode);

    /**
     * Destination warehouse id
     *
     * @return int|null
     */
    public function getDesWarehouseId();

    /**
     * Set destination warehouse id
     *
     * @param int|null $desWarehouseId
     * @return $this
     */
    public function setDesWarehouseId($desWarehouseId);

    /**
     * Destination warehouse code
     *
     * @return string|null
     */
    public function getDesWarehouseCode();

    /**
     * Set sestination warehouse code
     *
     * @param string|null $desWarehouseCode
     * @return $this
     */
    public function setDesWarehouseCode($desWarehouseCode);

    /**
     * External location
     *
     * @return string|null
     */
    public function getExternalLocation();

    /**
     * Set external location
     *
     * @param string|null $externalLocation
     * @return $this
     */
    public function setExternalLocation($externalLocation);

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
    
    /**
     * get Stock Transfer Id
     *
     * @return int|null
     */
    public function getStockTransferId();

    /**
     * set Stock Transfer Id
     *
     * @param int|null $stockTransferId
     * @return $this
     */
    public function setStockTransferId($stockTransferId);    
}