<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Warehouse;

use\Magento\Framework\Model\AbstractModel;
use Magestore\InventorySuccess\Api\Data\Warehouse\UpdateWarehouseStockRequestInterface;

class UpdateWarehouseStockRequest extends AbstractModel implements UpdateWarehouseStockRequestInterface
{
    /**
     * @inheritDoc
     */
    public function getWarehouseCode()
    {
        return $this->_getData(self::WAREHOUSE_CODE);
    }

    /**
     * @inheritDoc
     */
    public function setWarehouseCode($warehouseCode)
    {
        return $this->setData(self::WAREHOUSE_CODE, $warehouseCode);
    }

    /**
     * @inheritDoc
     */
    public function getOperator()
    {
        return $this->_getData(self::OPERATOR);
    }

    /**
     * @inheritDoc
     */
    public function setOperator($operator)
    {
        return $this->setData(self::OPERATOR, $operator);
    }

    /**
     * @inheritDoc
     */
    public function getProductSku()
    {
        return $this->_getData(self::PRODUCT_SKU);
    }

    /**
     * @inheritDoc
     */
    public function setProductSku($productSku)
    {
        return $this->setData(self::PRODUCT_SKU, $productSku);
    }

    /**
     * @inheritDoc
     */
    public function getQty()
    {
        return $this->_getData(self::QTY);
    }

    /**
     * @inheritDoc
     */
    public function setQty($qty)
    {
        if ($qty < 0) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Qty must be non-negative')
            );
        }
        return $this->setData(self::QTY, $qty);
    }
}