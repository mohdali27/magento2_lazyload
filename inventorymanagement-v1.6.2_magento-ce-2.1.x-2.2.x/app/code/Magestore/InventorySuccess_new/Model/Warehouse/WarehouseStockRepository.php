<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Warehouse;

use Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRepositoryInterface;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;

class WarehouseStockRepository implements WarehouseStockRepositoryInterface
{
    /**
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\WarehouseFactory
     */
    protected $warehouseFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\Warehouse\ProductFactory
     */
    protected $_warehouseProductFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\CollectionFactory
     */
    protected $_warehouseProductCollectionFactory;

    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface
     */
    protected $warehouseStockRegistry;

    /**
     * @var \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface
     */
    protected $stockChange;

    /**
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        \Magestore\InventorySuccess\Model\Warehouse\ProductFactory $warehouseProductFactory,
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\CollectionFactory $warehouseProductCollectionFactory,
        \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegistry,
        \Magento\Catalog\Api\Data\ProductSearchResultsInterfaceFactory $searchResultsFactory,
        \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface $stockChange
    )
    {
        $this->_objectManager = $objectManager;
        $this->productFactory = $productFactory;
        $this->warehouseFactory = $warehouseFactory;
        $this->_warehouseProductFactory = $warehouseProductFactory;
        $this->_warehouseProductCollectionFactory = $warehouseProductCollectionFactory;
        $this->warehouseStockRegistry = $warehouseStockRegistry;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->stockChange = $stockChange;
    }

    /**
     * {@inheritdoc}
     */
    public function get($warehouseId, $productSku)
    {
        $productId = $this->resolveProductId($productSku);
        return $this->warehouseStockRegistry->getStock($warehouseId, $productId);
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var $collection \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection */
        $collection = $this->_warehouseProductCollectionFactory->create();
        $collection->getSelect()->joinInner(
            ['catalog_product' => $collection->getTable('catalog_product_entity')],
            'main_table.product_id = catalog_product.entity_id',
            ['product_sku' => 'catalog_product.sku']
        )->joinInner(
            ['warehouse' => $collection->getTable('os_warehouse')],
            'main_table. ' . WarehouseProductInterface::WEBSITE_ID . ' = warehouse.warehouse_id',
            ['warehouse_id', 'warehouse_code', 'warehouse_name']
        );

        //Add filters from root filters group to the collection
        foreach ($searchCriteria->getFilterGroups() as $group) {
            $this->addFilterGroupToCollection($group, $collection);
        }

        /** @var SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder(
                $field,
                ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
            );
        }
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->load();
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());
        return $searchResult;
    }


    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param \Magento\Framework\Api\Search\FilterGroup $filterGroup
     * @param \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection $collection
     * @return void
     */
    protected function addFilterGroupToCollection(
        \Magento\Framework\Api\Search\FilterGroup $filterGroup,
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection $collection
    )
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $conditionType = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $field = $this->getRealFieldFromAlias($filter->getField());
            $collection->addFieldToFilter($field, [$conditionType => $filter->getValue()]);
        }

    }

    protected function getRealFieldFromAlias($field)
    {
        switch ($field) {
            case 'product_sku':
                $field = "catalog_product.sku";
                break;
            default:
                break;
        }
        return $field;
    }

    /**
     * {@inheritdoc}
     */
    public function getWarehouseStockBySku($warehouseId, $productSku)
    {
        $productId = $this->resolveProductId($productSku);
        return $this->warehouseStockRegistry->getStock($warehouseId, $productId);
    }

    /**
     * {@inheritdoc}
     */
    public function updateWarehouseStockBySku($warehouseStocks)
    {
        $result = array();
        foreach ($warehouseStocks as $warehouseStock) {
            $productId = $this->resolveProductId($warehouseStock->getProductSku());
            $warehouseId = $this->resolveWarehouseId($warehouseStock->getWarehouseCode());
            switch ($warehouseStock->getOperator()) {
                case \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface::QTY_UPDATE_ACTION:
                    $this->stockChange->update($warehouseId, $productId, $warehouseStock->getQty());
                    break;
                case \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface::QTY_INCREASE_ACTION:
                    $this->stockChange->increase($warehouseId, $productId, $warehouseStock->getQty());
                    break;
                case \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface::QTY_DECREASE_ACTION:
                    $this->stockChange->decrease($warehouseId, $productId, $warehouseStock->getQty());
                    break;
                default:
                    throw new \Magento\Framework\Webapi\Exception(
                        __('Operator not allowed!'),
                        0,
                        \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST
                    );
            }

            $warehouseProduct = $this->getWarehouseStockBySku($warehouseId, $warehouseStock->getProductSku());
            array_push($result, $warehouseProduct);
        }
        return $result;
    }

    /**
     * @param string $productSku
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function resolveProductId($productSku)
    {
        $product = $this->productFactory->create();
        $productId = $product->getIdBySku($productSku);
        if (!$productId) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __(
                    'Product with SKU "%1" does not exist',
                    $productSku
                )
            );
        }
        return $productId;
    }

    /**
     * @param string $warehouseCode
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function resolveWarehouseId($warehouseCode)
    {
        /**
         * @var $warehouse \Magestore\InventorySuccess\Model\Warehouse
         */
        $warehouse = $this->warehouseFactory->create();
        $warehouse->getResource()->load($warehouse, $warehouseCode, 'warehouse_code');
        $warehouseId = $warehouse->getWarehouseId();
        if (!$warehouseId) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __(
                    'Location with code "%1" does not exist',
                    $warehouseCode
                )
            );
        }
        return $warehouseId;
    }
}