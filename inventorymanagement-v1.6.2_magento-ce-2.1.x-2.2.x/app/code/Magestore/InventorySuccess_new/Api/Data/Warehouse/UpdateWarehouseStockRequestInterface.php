<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Data\Warehouse;

interface UpdateWarehouseStockRequestInterface
{
    const WAREHOUSE_CODE = 'warehouse_code';

    const OPERATOR = 'operator';

    const PRODUCT_SKU = 'product_sku';

    const QTY = 'qty';

    /**
     * @return string|null
     */
    public function getWarehouseCode();

    /**
     * @param string|null $warehouseCode
     * @return $this
     */
    public function setWarehouseCode($warehouseCode);

    /**
     *
     * @return string|null
     */
    public function getOperator();

    /**
     *
     * @param string $operator
     * @return $this
     */
    public function setOperator($operator);

    /**
     * @return string|null
     */
    public function getProductSku();

    /**
     * @param string|null $productSku
     * @return $this
     */
    public function setProductSku($productSku);

    /**
     *
     * @return string|null
     */
    public function getQty();

    /**
     *
     * @param int $qty
     * @return $this
     * @throws \Magento\Framework\Exception\ValidatorException
     */
    public function setQty($qty);
}