<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;

class Validator extends \Magento\CatalogRule\Model\Rule
{
    /**
     * @param \Amasty\Feed\Model\Feed $model
     * @param int $page
     * @param int $itemsPerPage
     * @param array $ids
     *
     * @return array
     */
    public function getValidProducts(\Amasty\Feed\Model\Feed $model, $page, $itemsPerPage, array $ids = [])
    {
        $isLastPage = false;

        /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $productCollection = $this->prepareCollection($model, $page, $itemsPerPage, $ids);
        if ($productCollection->getCurPage() >= $productCollection->getLastPageNumber()) {
            $isLastPage = true;
        }

        $this->_productIds[$page] = [];

        $products = $productCollection->getItems();

        /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
        foreach ($products as $product) {
            if ($this->validateProduct($model, $product)) {
                $this->_productIds[$page][] = [
                    'feed_id' => $model->getEntityId(),
                    'valid_product_id' => $product->getId()
                ];
            }
        }

        return ['isLastPage' => $isLastPage, 'productsId' => $this->_productIds];
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     *
     * @return bool
     */
    public function validateProduct(
        \Magento\Framework\Model\AbstractModel $model,
        \Magento\Catalog\Api\Data\ProductInterface $product
    ) {
        $product->setStoreId($model->getStoreId());

        return $model->getRule()->getConditions()->validate($product);
    }

    /**
     * @param \Amasty\Feed\Model\Feed $model
     * @param int $page
     * @param int $itemsPerPage
     * @param array $ids
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    private function prepareCollection(\Amasty\Feed\Model\Feed $model, $page, $itemsPerPage, $ids = [])
    {
        /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
        $productCollection = $this->_productCollectionFactory->create();
        $productCollection->addStoreFilter($model->getStoreId());

        if ($ids) {
            $productCollection->addAttributeToFilter('entity_id', ['in' => $ids]);
        }

        if ($this->_productsFilter) {
            $productCollection->addIdFilter($this->_productsFilter);
        }

        // DBEST-1250
        if ($model->getExcludeDisabled()) {
            $productCollection->addAttributeToFilter('status', ['eq' => Status::STATUS_ENABLED]);
        }
        if ($model->getExcludeNotVisible()) {
            $productCollection->addAttributeToFilter('visibility', ['neq' => Visibility::VISIBILITY_NOT_VISIBLE]);
        }
        if ($model->getExcludeOutOfStock()) {
            $productCollection->getSelect()->joinInner(
                ['s' => $productCollection->getTable('cataloginventory_stock_item')],
                $productCollection->getSelect()->getConnection()->quoteInto(
                    's.product_id = e.entity_id AND s.is_in_stock = ?',
                    1,
                    \Zend_Db::INT_TYPE
                ),
                'is_in_stock'
            );
        }

        $productCollection->setPage($page, $itemsPerPage);
        $model->getRule()->getConditions()->collectValidatedAttributes($productCollection);

        return $productCollection;
    }
}
