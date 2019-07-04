<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model;

class Rule extends \Magento\CatalogRule\Model\Rule
{
    public function getFeedMatchingProductIds() //skip afterGetMatchingProductIds plugin
    {
        if ($this->_productIds === null) {

            $this->_productIds = [];
            $this->setCollectedAttributes([]);

            /** @var $productCollection \Magento\Catalog\Model\ResourceModel\Product\Collection */
            $productCollection = $this->_productCollectionFactory->create();
            $productCollection->addStoreFilter($this->getStoreId());
            if ($this->_productsFilter) {
                $productCollection->addIdFilter($this->_productsFilter);
            }

            $this->getConditions()->collectValidatedAttributes($productCollection);

            $this->_registry->register('fee_matching_product_ids', $productCollection->getAllIds());
            $this->_resourceIterator->walk(
                $productCollection->getSelect(),
                [[$this, 'callbackValidateProduct']],
                [
                    'attributes' => $this->getCollectedAttributes(),
                    'product'    => $this->_productFactory->create()
                ]
            );
        }

        return $this->_productIds;
    }

    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);

        $results = [];

        $product->setStoreId($this->getStoreId());

        $validate = $this->getConditions()->validate($product);

        if ($validate) {
            $results[$this->getStoreId()] = $validate;
            $this->_productIds[$product->getId()] = $results;

        }
    }
}
