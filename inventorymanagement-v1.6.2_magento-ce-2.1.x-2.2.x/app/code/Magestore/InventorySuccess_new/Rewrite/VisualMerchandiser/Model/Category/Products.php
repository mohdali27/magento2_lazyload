<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Rewrite\VisualMerchandiser\Model\Category;

use \Magento\Framework\DB\Select;

class Products
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magento\VisualMerchandiser\Model\Position\Cache
     */
    protected $_cache;

    /**
     * @var string
     */
    protected $_cacheKey;

    /**
     * @var \Magento\VisualMerchandiser\Model\Sorting
     */
    protected $sorting;

    /**
     * Products constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\VisualMerchandiser\Model\Sorting $sorting
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_productFactory = $productFactory;
        $this->_moduleManager = $moduleManager;
        $this->cache = $this->_objectManager->create('Magestore\InventorySuccess\Rewrite\VisualMerchandiser\Model\Position\Cache');
        $this->sorting = $this->_objectManager->create('\Magento\VisualMerchandiser\Model\Sorting');
    }

    /**
     * @param string $key
     * @return void
     */
    public function setCacheKey($key)
    {
        $this->_cacheKey = $key;
    }

    /**
     * @return \Magento\Catalog\Model\ProductFactory
     */
    public function getFactory()
    {
        return $this->_productFactory;
    }

    /**
     * @param int $categoryId
     * @param int $store
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getCollectionForGrid($categoryId, $store = null)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->getFactory()->create()
            ->getCollection()
            ->addAttributeToSelect([
                'sku',
                'name',
                'price',
                'small_image'
            ]);

        if ($store !== null) {
            $collection->addStoreFilter($store);
        }

        $collection->getSelect()
            ->where('at_position.category_id = ?', $categoryId);

        if ($this->_moduleManager->isEnabled('Magento_CatalogInventory')) {
            $collection->joinField(
                'stock',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                ['stock_id' => $this->getStockId(),'website_id' => '0'],
                'left'
            );
        }
        $cache = $this->_cache->getPositions($this->_cacheKey);

        if ($cache === false) {
            $collection->joinField(
                'position',
                'catalog_category_product',
                'position',
                'product_id=entity_id',
                null,
                'left'
            );
            $collection->setOrder('position', $collection::SORT_ORDER_ASC);

            // Cache the positions initially
            $_collection = clone $collection;

            $positions = [];
            $idx = 0;
            foreach ($_collection as $item) {
                $positions[$item->getId()] = $idx;
                $idx++;
            }

            $this->_cache->saveData($this->_cacheKey, $positions);
        } else {
            $collection->getSelect()
                ->reset(Select::WHERE)
                ->reset(Select::HAVING);

            $collection->addAttributeToFilter('entity_id', ['in' => array_keys($cache)]);
        }
        return $collection;
    }

    /**
     * @return int
     */
    protected function getStockId()
    {
        return \Magento\CatalogInventory\Model\Stock::DEFAULT_STOCK_ID;
    }

    /**
     * Apply cached positions, sort order products
     * returns a base collection with WHERE IN filter applied
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function applyCachedChanges(\Magento\Catalog\Model\ResourceModel\Product\Collection $collection)
    {
        $positions = $this->_cache->getPositions($this->_cacheKey);

        if ($positions === false || count($positions) === 0) {
            return $collection;
        }

        $collection->getSelect()->reset(Select::ORDER);
        asort($positions, SORT_NUMERIC);

        $ids = implode(',', array_keys($positions));
        $field = $collection->getSelect()->getAdapter()->quoteIdentifier('e.entity_id');
        $collection->getSelect()->order(new \Zend_Db_Expr("FIELD({$field}, {$ids})"));

        $sortOrder = $this->_cache->getSortOrder($this->_cacheKey);
        $sortBuilder = $this->sorting->getSortingInstance($sortOrder);

        $sortedCollection = $sortBuilder->sort($collection);

        $idx = 0;
        $positions = [];
        foreach ($sortedCollection as $item) {
            $positions[$item->getId()] = $idx;
            $idx++;
        }

        $this->savePositionsToCache($positions);

        return $sortedCollection;
    }

    /**
     * Save products positions to cache
     *
     * @param array $positions
     * @return void
     */
    protected function savePositionsToCache($positions)
    {
        $this->_cache->saveData(
            $this->_cacheKey,
            $positions
        );
    }
}
