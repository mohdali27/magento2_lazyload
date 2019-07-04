<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\AdjustStock;

use \Magento\Framework\Model\AbstractModel;
use \Magestore\InventorySuccess\Api\Data\AdjustStock\ProductInterface;
use \Magestore\InventorySuccess\Api\StockActivity\StockActivityProductInterface;

/**
 * Class Product
 * @package Magestore\InventorySuccess\Model\AdjustStock
 */
class Product extends AbstractModel implements ProductInterface, StockActivityProductInterface
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct(){
        parent::_construct();
        $this->_init('Magestore\InventorySuccess\Model\ResourceModel\AdjustStock\Product');
    }

    /**
     * @inheritDoc
     */
    public function getAdjuststockProductId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setAdjuststockProductId($id)
    {
        return $this->setData(self::ID, $id);
    }


    /**
     * @inheritDoc
     */
    public function getAdjuststockId()
    {
        return $this->getData(self::ADJUSTSTOCK_ID);
    }

    /**
     * @inheritDoc
     */
    public function setAdjuststockId($adjustStockId)
    {
        return $this->setData(self::ADJUSTSTOCK_ID, $adjustStockId);
    }

    /**
     * @inheritDoc
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @inheritDoc
     */
    public function getProductName()
    {
        return $this->getData(self::PRODUCT_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setProductName($productName)
    {
        return $this->setData(self::PRODUCT_NAME, $productName);
    }

    /**
     * @inheritDoc
     */
    public function getProductSku()
    {
        return $this->getData(self::PRODUCT_SKU);
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
    public function getOldQty()
    {
        return $this->getData(self::OLD_QTY);
    }

    /**
     * @inheritDoc
     */
    public function setOldQty($qty)
    {
        return $this->setData(self::OLD_QTY, $qty);
    }

    /**
     * @inheritDoc
     */
    public function getSuggestQty()
    {
        return $this->getData(self::SUGGEST_QTY);
    }

    /**
     * @inheritDoc
     */
    public function setSuggestQty($qty)
    {
        return $this->setData(self::SUGGEST_QTY, $qty);
    }

    /**
     * @inheritDoc
     */
    public function getAdjustQty()
    {
        return $this->getData(self::ADJUST_QTY);
    }

    /**
     * @inheritDoc
     */
    public function setAdjustQty($qty)
    {
        return $this->setData(self::ADJUST_QTY, $qty);
    }


}
