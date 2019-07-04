<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Plugin\CatalogInventory\Model\ResourceModel\Stock;

use Magento\CatalogInventory\Model\Stock;
use Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface;

/**
 * Class Status
 * @package Magestore\InventorySuccess\Plugin\CatalogInventory\Model\ResourceModel\Stock
 */
class Status extends \Magento\CatalogInventory\Model\ResourceModel\Stock\Status
{
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     *
     * @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface;
     */
    protected $warehouseManagement;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        WarehouseManagementInterface $warehouseManagement,
        $connectionName = null
    )
    {
        parent::__construct($context, $storeManager, $websiteFactory, $eavConfig, $connectionName);
        $this->stockConfiguration = $stockConfiguration;
        $this->warehouseManagement = $warehouseManagement;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param bool $isFilterInStock
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    public function addStockDataToCollection($collection, $isFilterInStock)
    {
        if (!$this->warehouseManagement->isGetStockFromWarehouse()) {
            return parent::addStockDataToCollection($collection, $isFilterInStock);
        }
        $websiteId = $this->stockConfiguration->getDefaultScopeId();
        $warehouseId = $this->warehouseManagement->getCurrentWarehouseByStore()->getWarehouseId();
        if (!$warehouseId)
            $warehouseId = Stock::DEFAULT_STOCK_ID;
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
            ['stock_status_index' => $this->getMainTable()],
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
}