<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\Warehouse;

use Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

/**
 * Class Product
 * @package Magestore\InventorySuccess\Model\ResourceModel\Warehouse
 */
class Product extends \Magestore\InventorySuccess\Model\ResourceModel\AbstractResource
{
    const MAIN_TABLE = 'cataloginventory_stock_item';
    const PRIMARY_KEY = 'item_id';

    /**
     * @var WarehouseManagementInterface
     */
    protected $warehouseManagement;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManagement;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     *
     * @param QueryProcessorInterface $queryProcessor
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string $connectionName
     */
    public function __construct(
        WarehouseManagementInterface $warehouseManagement,
        StoreManagerInterface $storeManagement,
        \Magestore\InventorySuccess\Api\Db\QueryProcessorInterface $queryProcessor,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    )
    {
        $this->warehouseManagement = $warehouseManagement;
        $this->storeManagement = $storeManagement;
        parent::__construct($queryProcessor, $context, $connectionName);
    }

    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::MAIN_TABLE, self::PRIMARY_KEY);
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param bool $isFilterInStock
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    public function addStockDataToCollection($collection, $isFilterInStock)
    {
        $warehouseId = $this->warehouseManagement->getCurrentWarehouseByStore()->getWarehouseId();
        if (!$warehouseId)
            $warehouseId = Stock::DEFAULT_STOCK_ID;
        $websiteId = $this->getStockConfiguration()->getDefaultScopeId();

        $joinCondition = $this->getConnection()->quoteInto(
            'e.entity_id = stock_status_index.product_id' . ' AND stock_status_index.website_id = ?',
            $websiteId
        );

        $joinCondition .= $this->getConnection()->quoteInto(
            ' AND stock_status_index.stock_id = ?',
            $warehouseId
        );
        $method = $isFilterInStock ? 'join' : 'joinLeft';
        $collection->getSelect()->$method(
            ['stock_status_index' => $this->getTable('cataloginventory_stock_status')],
            $joinCondition,
            ['is_salable' => 'stock_status']
        );

        if ($isFilterInStock) {
            $collection->getSelect()->where(
                'stock_status_index.stock_status = ?',
                Stock\Status::STATUS_IN_STOCK
            );
        }
        return $collection;
    }

    public function addStockDataToChildProducts($collection, $isFilterInStock)
    {
        $warehouseId = $this->warehouseManagement->getCurrentWarehouseByStore()->getWarehouseId();
        $websiteId = $this->getStockConfiguration()->getDefaultScopeId();
        $minQty = $this->getStockConfiguration()->getMinQty();

        /* join to warehouse_product */
        $joinCondition = 'e.entity_id = wp.product_id';
        $joinCondition .= $this->getConnection()->quoteInto(
            ' AND wp.' . WarehouseProductInterface::WAREHOUSE_ID . ' = ?',
            $warehouseId
        );
        $method = 'joinLeft';
        $collection->getSelect()->$method(
            ['wp' => $this->getMainTable()],
            $joinCondition,
            ['total_qty', 'qty_to_ship']
        );

        $collection->getSelect()->columns([
            'is_salable' => "IF((wp.total_qty - wp.qty_to_ship) > $minQty, 1, 0)",
            'stock_status' => "IF((wp.total_qty - wp.qty_to_ship) > $minQty, 1, 0)",
        ]);

        /* filter stock status*/
        if ($isFilterInStock) {
            $collection->getSelect()->where(
                "IF((wp.total_qty - wp.qty_to_ship) > $minQty, 1, 0) = ?",
                1
            );
        }

        return $collection;
    }

    /**
     * @return StockConfigurationInterface
     */
    public function getStockConfiguration()
    {
        if ($this->stockConfiguration === null) {
            $this->stockConfiguration = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\CatalogInventory\Api\StockConfigurationInterface');
        }
        return $this->stockConfiguration;
    }
}