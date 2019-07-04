<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Data\Warehouse;

/**
 * Interface WarehouseProduct
 * @api
 */
interface ProductInterface extends \Magento\Framework\Api\CustomAttributesDataInterface
{
    CONST WAREHOUSE_PRODUCT_ID = "item_id";

    CONST WAREHOUSE_ID = "website_id";

    const PRODUCT_ID = "product_id";

    const TOTAL_QTY = "total_qty";
    
    const AVAILABLE_QTY = "qty";

    CONST QTY_TO_SHIP = "qty_to_ship";

    CONST SHELF_LOCATION = "shelf_location";

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';
    
    const WEBSITE_ID = 'website_id';
    
    const STOCK_ID = 'stock_id';    
    
    
    const DEFAULT_SCOPE_ID = 0;

    /**
     * @return int|null
     */
    public function getWarehouseProductId();

    /**
     * @return int|null
     */
    public function getWarehouseId();

    /**
     * @param int $warehouseId
     * @return $this
     */
    public function setWarehouseId($warehouseId);

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
     * @return float
     */
    public function getTotalQty();

    /**
     * @param float $totalQty
     * @return $this
     */
    public function setTotalQty($totalQty);
    
    /**
     * @return float
     */
    public function getAvailableQty();

    /**
     * @param float $qty
     * @return $this
     */
    public function setAvailableQty($qty);    

    /**
     * @return float
     */
    public function getQtyToShip();

    /**
     * @param float $qtyToShip
     * @return $this
     */
    public function setQtyToShip($qtyToShip);

    /**
     * @return null|string
     */
    public function getShelfLocation();

    /**
     * @param string $shelfLocation
     * @return $this
     */
    public function setShelfLocation($shelfLocation);

    /**
     * Created at
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created at
     *
     * @param int $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Updated at
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated at
     *
     * @param int $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}