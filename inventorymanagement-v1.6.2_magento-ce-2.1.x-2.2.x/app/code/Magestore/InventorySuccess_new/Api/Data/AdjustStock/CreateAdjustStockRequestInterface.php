<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Data\AdjustStock;

interface CreateAdjustStockRequestInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const ADJUSTSTOCK_ID = 'adjuststock_id';
    const ADJUSTSTOCK_CODE = 'adjuststock_code';
    const WAREHOUSE_ID = 'warehouse_id';
    const WAREHOUSE_NAME = 'warehouse_name';
    const WAREHOUSE_CODE = 'warehouse_code';
    const REASON = 'reason';
    const CREATED_BY = 'created_by';
    const CREATED_AT = 'created_at';
    const CONFIRMED_BY = 'confirmed_by';
    const CONFIRMED_AT = 'confirmed_at';
    const STATUS = 'status';
    const ACTION = 'action';
    const KEY_PRODUCTS = 'products';

    /**
     * Constants defined Statuses
     */
    const STATUS_PENDING = 0;
    const STATUS_COMPLETED = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_CANCELED = 3;

    /**
     * Prefix code (using for generate the adjustment code)
     */
    const PREFIX_CODE = 'ADJ';

    /**
     * Get Id
     *
     * @return int|null
     */
    public function getId();

    /**
     *
     * @param int $id
     * @return AdjustStockInterface
     */
    public function setId($id);

    /**
     * @return int|null
     */
    public function getAdjustStockId();

    /**
     * @param int $adjustStockId
     * @return $this
     */
    public function setAdjustStockId($adjustStockId);

    /**
     * Get adjuststock code
     *
     * @return string
     */
    public function getAdjustStockCode();

    /**
     *
     * @param string $adjustStockCode
     * @return AdjustStockInterface
     */
    public function setAdjustStockCode($adjustStockCode);

    /**
     * Get Warehouse Id
     *
     * @return int
     */
    public function getWarehouseId();

    /**
     *
     * @param int $warehouseId
     * @return AdjustStockInterface
     */
    public function setWarehouseId($warehouseId);

    /**
     * @return string
     */
    public function getWarehouseCode();

    /**
     *
     * @param string $warehouseCode
     * @return AdjustStockInterface
     */
    public function setWarehouseCode($warehouseCode);

    /**
     * Get Warehouse Name
     *
     * @return string|null
     */
    public function getWarehouseName();

    /**
     *
     * @param string $warehouseName
     * @return AdjustStockInterface
     */
    public function setWarehouseName($warehouseName);

    /**
     * Get Reason
     *
     * @return string|null
     */
    public function getReason();

    /**
     *
     * @param string $reason
     * @return AdjustStockInterface
     */
    public function setReason($reason);

    /**
     * @return string
     */
    public function getCreatedBy();

    /**
     *
     * @param string $createdBy
     * @return AdjustStockInterface
     */
    public function setCreatedBy($createdBy);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     *
     * @param string $createdAt
     * @return AdjustStockInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getConfirmedBy();

    /**
     *
     * @param string $confirmedBy
     * @return AdjustStockInterface
     */
    public function setConfirmedBy($confirmedBy);

    /**
     * @return string
     */
    public function getConfirmedAt();

    /**
     *
     * @param string $confirmedAt
     * @return AdjustStockInterface
     */
    public function setConfirmedAt($confirmedAt);

    /**
     * @return int
     */
    public function getStatus();

    /**
     *
     * @param int $status
     * @return AdjustStockInterface
     */
    public function setStatus($status);

    /**
     * @return string|null
     */
    public function getAction();

    /**
     * @param string $action
     * @return $this
     */
    public function setAction($action);

    /**
     * Get adjust stock products.
     *
     * @return \Magestore\InventorySuccess\Api\Data\AdjustStock\ProductInterface[]|null
     */
    public function getProducts();

    /**
     * Set adjust stock products.
     *
     * @param \Magestore\InventorySuccess\Api\Data\AdjustStock\ProductInterface[] $products
     * @return $this
     */
    public function setProducts(array $products = null);
}