<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Data\AdjustStock;

/**
 * Customer address interface.
 * @api
 */
interface ProductInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    CONST ID = 'adjuststock_product_id';
    CONST ADJUSTSTOCK_ID = 'adjuststock_id';
    CONST PRODUCT_ID = 'product_id';
    CONST PRODUCT_NAME = 'product_name';
    CONST PRODUCT_SKU = 'product_sku';
    CONST OLD_QTY = 'old_qty';
    CONST SUGGEST_QTY = 'suggest_qty';
    CONST ADJUST_QTY = 'adjust_qty';

    /**
     * @return int|null
     */
    public function getAdjuststockProductId();

    /**
     * @param int $id
     * @return $this
     */
    public function setAdjuststockProductId($id);

    /**
     * @return int|null
     */
    public function getAdjuststockId();

    /**
     * @param int $adjustStockId
     * @return $this
     */
    public function setAdjuststockId($adjustStockId);

    /**
     * @return int|null
     */
    public function getProductId();

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * @return string|null
     */
    public function getProductName();

    /**
     * @param string $productName
     * @return $this
     */
    public function setProductName($productName);

    /**
     * @return string|null
     */
    public function getProductSku();

    /**
     * @param string $productSku
     * @return $this
     */
    public function setProductSku($productSku);

    /**
     * @return int|null
     */
    public function getOldQty();

    /**
     * @param int $qty
     * @return $this
     */
    public function setOldQty($qty);

    /**
     * @return int|null
     */
    public function getSuggestQty();

    /**
     * @param int $qty
     * @return $this
     */
    public function setSuggestQty($qty);

    /**
     * @return int|null
     */
    public function getAdjustQty();

    /**
     * @param int $qty
     * @return $this
     */
    public function setAdjustQty($qty);
}