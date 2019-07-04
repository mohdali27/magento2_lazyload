<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\StockActivity;

use Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product as WarehouseProductResource;
use Magento\CatalogInventory\Model\ResourceModel\Stock;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magestore\InventorySuccess\Api\Db\QueryProcessorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Model\Stock as StockModel;

class StockChange extends Stock
{
    /**
     *
     * @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface
     */
    protected $_warehouseStockRegistry;

    /**
     *
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $_stockIndexerProcessor;

    /**
     *
     * @var \Magestore\InventorySuccess\Api\Db\QueryProcessorInterface
     */
    protected $_queryProcessor;

    /**
     * @var array
     */
    protected $stockIds;


    /**
     * StockChange constructor.
     * @param \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegistry
     * @param QueryProcessorInterface $queryProcessor
     * @param \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param StockConfigurationInterface $stockConfiguration
     * @param StoreManagerInterface $storeManager
     * @param null $connectionName
     */
    public function __construct(
        \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegistry,
        \Magestore\InventorySuccess\Api\Db\QueryProcessorInterface $queryProcessor,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        StockConfigurationInterface $stockConfiguration,
        StoreManagerInterface $storeManager,
        $connectionName = null
    )
    {
        $this->_warehouseStockRegistry = $warehouseStockRegistry;
        $this->_queryProcessor = $queryProcessor;
        $this->_stockIndexerProcessor = $stockIndexerProcessor;
        parent::__construct($context, $scopeConfig, $dateTime, $stockConfiguration, $storeManager, $connectionName);
    }

    protected function _construct()
    {
        /* do nothing */
    }

    /**
     * Change qty of product in Warehouse
     *
     * @param int $warehouseId
     * @param int $productId
     * @param float $qtyChange
     * @param bool $updateCatalog
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChange
     */
    public function change($warehouseId, $productId, $qtyChange, $updateCatalog = true)
    {
        return $this->massChange($warehouseId, [$productId => $qtyChange], $updateCatalog);
    }

    /**
     * Update qty of product in Warehouse
     *
     * @param int $warehouseId
     * @param int $productId
     * @param float $qtyUpdate
     * @param bool $updateCatalog
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChange
     */
    public function update($warehouseId, $productId, $qtyUpdate, $updateCatalog = true)
    {
        return $this->massUpdate($warehouseId, [$productId => $qtyUpdate], $updateCatalog);
    }

    /**
     * Decrease qty of product in Warehouse
     *
     * @param int $warehouseId
     * @param int $productId
     * @param float $qty
     * @param bool $updateCatalog
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChange
     */
    public function decrease($warehouseId, $productId, $qty, $updateCatalog = true)
    {
        return $this->change($warehouseId, $productId, -abs($qty), $updateCatalog);
    }

    /**
     * Increase qty of product in Warehouse
     *
     * @param int $warehouseId
     * @param int $productId
     * @param float $qty
     * @param bool $updateCatalog
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChange
     */
    public function increase($warehouseId, $productId, $qty, $updateCatalog = true)
    {
        return $this->change($warehouseId, $productId, abs($qty), $updateCatalog);
    }

    /**
     * Change qty of stocks in warehouse, also update global stocks
     *
     * @param int $warehouseId
     * @param array $qtys
     * @param bool $updateCatalog
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChange
     */
    public function massChange($warehouseId, $qtys, $updateCatalog = true)
    {
        $this->massUpdate($warehouseId, $qtys, $updateCatalog, StockChangeInterface::QTY_CHANGE_ACTION);
        return $this;
    }

    /**
     * Update qty of stocks in warehouse, also update global stocks
     *
     * @param $warehouseId
     * @param $qtys
     * @param bool $updateCatalog
     * @param null $actiontype
     * @return $this
     */
    public function massUpdate($warehouseId, $qtys, $updateCatalog = true, $actiontype = null)
    {
        $actiontype = ($actiontype == StockChangeInterface::QTY_CHANGE_ACTION) ? $actiontype : StockChangeInterface::QTY_UPDATE_ACTION;

        /* start queries processing */
        $this->_queryProcessor->start();

        /* prepare to update stocks in warehouse, then add queries to Processor */
        $changeQtys = $this->_prepareUpdateWarehouseStocks($warehouseId, $qtys, $actiontype);

        /* prepare to update global stocks, then add queries to Processor */
        if($updateCatalog) {
            $this->_prepareUpateGlobalStocks($changeQtys);
        }

        /* process queries in Processor */
        $this->_queryProcessor->process();

        /* reindex stock data */
        $this->_reindexStockData(array_keys($changeQtys));

        /* clean product cache */
        $this->_cleanCache();

        return $this;
    }

    /**
     * Process queries in queue
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChange
     */
    protected function _processQueuedQueries()
    {
        $this->_queryProcessor->process();
        return $this;
    }

    /**
     * Prepare queries to update/change qty of stocks in warehouse, return all changed qty items
     *
     * @param int $warehouseId
     * @param array $qtys
     * @return array
     */
    protected function _prepareUpdateWarehouseStocks($warehouseId, $qtys, $actionType)
    {
        $warehouseProducts = $this->_warehouseStockRegistry->getStocks($warehouseId, array_keys($qtys));
        $connection = $this->getConnection();
        $changeQtys = [];
        $newQtys = $qtys;

        /* update stocks in Warehouse */
        if ($warehouseProducts->getSize()) {
            /* update existed products in warehouse */
            $conditions = [];
            foreach ($warehouseProducts as $warehouseProduct) {
                /* calculate changed qty */
                if($actionType == StockChangeInterface::QTY_CHANGE_ACTION) {
                    $changeQty = $qtys[$warehouseProduct->getProductId()];
                } elseif($actionType == StockChangeInterface::QTY_UPDATE_ACTION) {
                    $changeQty = $qtys[$warehouseProduct->getProductId()] - $warehouseProduct->getTotalQty() ;
                }
                $changeQtys[$warehouseProduct->getProductId()] = $changeQty;
                unset($newQtys[$warehouseProduct->getProductId()]);
                /* prepare update value */
                $case = $connection->quoteInto('?', $warehouseProduct->getProductId());
                $operator = $changeQty >= 0 ? '+' : '-';
                $totalQtyConditions[$case] = $connection->quoteInto("total_qty{$operator}?", abs($changeQty));;
                $qtyConditions[$case] = $connection->quoteInto("qty{$operator}?", abs($changeQty));;
            }
            $values = array(
                'total_qty' => $connection->getCaseSql('product_id', $totalQtyConditions, 'total_qty'),
                'qty' => $connection->getCaseSql('product_id', $qtyConditions, 'qty'),
            );
            $where = [
                WarehouseProductInterface::PRODUCT_ID .' IN (?)' => array_keys($changeQtys),
                WarehouseProductInterface::WAREHOUSE_ID .'=?' => $warehouseId
            ];
            /* add query to the processor */
            $this->_queryProcessor->addQuery([
                'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
                'values' => $values,
                'condition' => $where,
                'table' => $this->getTable(WarehouseProductResource::MAIN_TABLE)
            ]);
        }

        /* add new products to warehouse */
        $this->_prepareAddProductsToWarehouse($warehouseId, $newQtys);

        /* return all changed items */
        $changeQtys += $newQtys;

        /* collect all changed items */
        $changeQtys += $newQtys;

        /* prepare to update in-stock status of products in warehouse */
        $queries[] = $this->_prepareUpdateInStockStatus(array_keys($changeQtys), $warehouseId);

        /* prepare to update out-stock status of products in warehouse */
        $queries[] = $this->_prepareUpdateOutStockStatus(array_keys($changeQtys), $warehouseId);

        return $changeQtys;
    }


    /**
     * Prepare to add new products to Warehouse
     *
     * @param int $warehouseId
     * @param array $newQtys
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChange
     */
    protected function _prepareAddProductsToWarehouse($warehouseId, $newQtys)
    {
        if(!count($newQtys)) {
            return $this;
        }
        /* add new products to warehouse */
        $insertData = [];
        foreach ($newQtys as $productId => $qty) {
            $changeQtys[$productId] = $qty;
            $insertData[] = [
                WarehouseProductInterface::WAREHOUSE_ID => $warehouseId,
                WarehouseProductInterface::WEBSITE_ID => $warehouseId,
                WarehouseProductInterface::PRODUCT_ID => $productId,
                WarehouseProductInterface::TOTAL_QTY => $qty,
                WarehouseProductInterface::AVAILABLE_QTY => $qty,
                WarehouseProductInterface::STOCK_ID => $warehouseId,
                'stock_status_changed_auto' => 1,
                'is_in_stock' => 1,
            ];
        }
        /* add query to the processor */
        $this->_queryProcessor->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
            'values' => $insertData,
            'table' => $this->getTable(WarehouseProductResource::MAIN_TABLE)
        ]);
        return $this;
    }

    /**
     * Prepare to update global stocks
     *
     * @param type $changeQtys
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChange
     */
    protected function _prepareUpateGlobalStocks($changeQtys)
    {
        /* prepare to update qty of global stocks */
        $this->_prepareUpdateGlobalStockQty($changeQtys);

        /* prepare to update in-stock status of global stocks */
        $this->_prepareUpdateInStockStatus(array_keys($changeQtys));

        /* prepare to update out-stock status of global stocks */
        $this->_prepareUpdateOutStockStatus(array_keys($changeQtys));

        return $this;
    }

    /**
     * Prepare query to update qty of global stocks
     *
     * @param array $changeQtys
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChange
     */
    protected function _prepareUpdateGlobalStockQty($changeQtys)
    {
        /* update global stocks */
        if (!count($changeQtys)) {
            return $this;
        }
        $connection = $this->getConnection();
        $conditions = [];
        $totalQtyConditions = [];
        $nullConditions = [];
        $nullTotalQtyConditions = [];
        foreach ($changeQtys as $productId => $qty) {
            $operator = $qty >= 0 ? '+' : '-';
            $case = $connection->quoteInto('?', $productId);
            $conditions[$case] = $connection->quoteInto("qty{$operator}?", abs($qty));
            $totalQtyConditions[$case] = $connection->quoteInto("total_qty{$operator}?", abs($qty));;
            /* in the case of qty is null */
            $nullConditions[$case] = $connection->quoteInto('?', $qty);
            $nullTotalQtyConditions[$case] = $connection->quoteInto('?', $qty);
        }

        /* add query to the processor */
            /* in the case of qty is not null */
        $this->_queryProcessor->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => [
                'qty' => $connection->getCaseSql('product_id', $conditions, 'qty'),
                'total_qty' => $connection->getCaseSql('product_id', $totalQtyConditions, 'total_qty'),
            ],
            'condition' => [
                'product_id IN (?)' => array_keys($changeQtys),
                'stock_id =?' => \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID,
                'website_id =?' => WarehouseProductInterface::DEFAULT_SCOPE_ID,
                'qty IS NOT NULL'
            ],
            'table' => $this->getTable('cataloginventory_stock_item')
        ]);

            /* in the case of qty is null */
        $this->_queryProcessor->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => [
                'qty' => $connection->getCaseSql('product_id', $nullConditions, 'qty'),
                'total_qty' => $connection->getCaseSql('product_id', $nullTotalQtyConditions, 'total_qty'),
            ],
            'condition' => [
                'product_id IN (?)' => array_keys($changeQtys),
                'stock_id =?' => \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID,
                'website_id =?' => WarehouseProductInterface::DEFAULT_SCOPE_ID,
                'qty IS NULL'
            ],
            'table' => $this->getTable('cataloginventory_stock_item')
        ]);

        return $this;
    }


    /**
     * Prepare query to update out-stock status of products
     *
     * @param array $productIds
     * @param int $website_id
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChange
     */
    protected function _prepareUpdateOutStockStatus($productIds, $websiteId = 0)
    {
        $websiteId = $websiteId ? $websiteId : WarehouseProductInterface::DEFAULT_SCOPE_ID;
        $this->_initConfig();
        $insConnection = $this->getConnection();
        $insValues = ['is_in_stock' => StockModel::STOCK_OUT_OF_STOCK, 'stock_status_changed_auto' => 1];

        $insSelect = $insConnection->select()->from($this->getTable('catalog_product_entity'), 'entity_id')
            ->where('type_id IN(?)', $this->_configTypeIds)
            ->where('entity_id IN(?)', $productIds);

        $where = sprintf(
            'website_id = %1$d' .
            ' AND is_in_stock = 1' .
            ' AND ((use_config_manage_stock = 1 AND 1 = %2$d) OR (use_config_manage_stock = 0 AND manage_stock = 1))' .
            ' AND ((use_config_backorders = 1 AND %3$d = %4$d) OR (use_config_backorders = 0 AND backorders = %3$d))' .
            ' AND ((use_config_min_qty = 1 AND qty <= %5$d) OR (use_config_min_qty = 0 AND qty <= min_qty))' .
            ' AND product_id IN (%6$s)',
            $websiteId,
            $this->_isConfigManageStock,
            \Magento\CatalogInventory\Model\Stock::BACKORDERS_NO,
            $this->_isConfigBackorders,
            $this->_configMinQty,
            $insSelect->assemble()
        );
        /* add query to the processor */
        $this->_queryProcessor->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => $insValues,
            'condition' => $where,
            'table' => $this->getTable('cataloginventory_stock_item')
        ]);
        return $this;
    }

    /**
     * Prepare query to update in-stock status of products
     *
     * @param array $productIds
     * @param int $website_id
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChange
     */
    protected function _prepareUpdateInStockStatus($productIds, $websiteId = 0)
    {
        $websiteId = $websiteId ? $websiteId : WarehouseProductInterface::DEFAULT_SCOPE_ID;
        $this->_initConfig();
        $insConnection = $this->getConnection();
        $insValues = ['is_in_stock' => StockModel::STOCK_IN_STOCK];

        $insSelect = $insConnection->select()->from($this->getTable('catalog_product_entity'), 'entity_id')
            ->where('type_id IN(?)', $this->_configTypeIds)
            ->where('entity_id IN(?)', $productIds);

        $where = sprintf(
            'website_id = %1$d' .
            ' AND is_in_stock = 0' .
            ' AND stock_status_changed_auto = 1' .
            ' AND ((use_config_manage_stock = 1 AND 1 = %2$d) OR (use_config_manage_stock = 0 AND manage_stock = 1))' .
            ' AND ((use_config_min_qty = 1 AND qty > %3$d) OR (use_config_min_qty = 0 AND qty > min_qty))' .
            ' AND product_id IN (%4$s)',
            $websiteId,
            $this->_isConfigManageStock,
            $this->_configMinQty,
            $insSelect->assemble()
        );
        /* add query to the processor */
        $this->_queryProcessor->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
            'values' => $insValues,
            'condition' => $where,
            'table' => $this->getTable('cataloginventory_stock_item')
        ]);
        return $this;
    }

    /**
     * Reindex stock data of products
     *
     * @param array $productIds
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChange
     */
    protected function _reindexStockData($productIds)
    {
        /* reindex stock data */
        if (count($productIds)) {
            $this->_stockIndexerProcessor->reindexList($productIds);
        }
        return $this;
    }

    /**
     * Clean product cache (frontend)
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChange
     */
    protected function _cleanCache()
    {
        $cacheManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
            'Magento\Framework\App\CacheInterface'
        )->clean([\Magento\Catalog\Model\Product::CACHE_TAG]);
        return $this;
    }
}
