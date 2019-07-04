<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\StockActivity;

use Magestore\InventorySuccess\Model\ResourceModel\AbstractResource;
use Magestore\InventorySuccess\Api\Db\QueryProcessorInterface;
use Magestore\InventorySuccess\Api\StockActivity\StockActivityInterface;

class ProductSelectionManagement extends AbstractResource
{
    protected function _construct()
    {
        /* do nothing */
    }

    /**
     * Add products to StockActivity
     * 
     * @param StockActivityInterface $stockActivity
     * @param array $data
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\ProductSelectionManagement
     */
    public function addProducts(StockActivityInterface $stockActivity, $data)
    {
        /* start queries processing */
        $this->_queryProcessor->start();

        /* prepare to add products to StockActivity, then add queries to Processor */
        $this->_prepareAddProducts($stockActivity, $data);

        /* process queries in Processor */
        $this->_queryProcessor->process();

        return $this;
    }

    /**
     * Set products to StockActivity
     * 
     * @param StockActivityInterface $stockActivity
     * @param array $data
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\ProductSelectionManagement
     */
    public function setProducts(StockActivityInterface $stockActivity, $data)
    {
        /* start queries processing */
        $this->_queryProcessor->start();

        /* remove existed products not in $data
         * update existed products in $data
         * add new products
         * add queries to Processor 
         */
        $this->_prepareSetProducts($stockActivity, $data);

        /* process queries in Processor */
        $this->_queryProcessor->process();

        return $this;
    }

    /**
     * Get products from StockActivity
     * 
     * @param StockActivityInterface $stockActivity
     * @param array $productIds
     */
    public function getProducts(StockActivityInterface $stockActivity, $productIds = [])
    {
        $stockActivityResource = $stockActivity->getResource();
        $collection = $stockActivity->getStockActivityProductModel()->getCollection();

        $stockActivityProducts = $collection->addFieldToFilter($stockActivityResource->getIdFieldName(), $stockActivity->getId());
        if (count($productIds)) {
            $stockActivityProducts->addFieldToFilter('product_id', ['in' => $productIds]);
        }
        return $stockActivityProducts;
    }

    /**
     * Remove products from StockActivity
     * 
     * @param StockActivityInterface $stockActivity
     * @param array $productIds
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\ProductSelectionManagement
     */
    public function removeProducts(StockActivityInterface $stockActivity, $productIds = [])
    {
        /* start queries processing */
        $this->_queryProcessor->start();

        /* prepate to remove products from StockActivity, then add queries to Processor */
        $this->_prepareRemoveProducts($stockActivity, $productIds);

        /* process queries in Processor */
        $this->_queryProcessor->process();

        return $this;
    }

    /**
     * Remove all products from StockActivity
     * 
     * @param StockActivityInterface $stockActivity
     */
    public function removeAllProducts(StockActivityInterface $stockActivity)
    {
        $this->removeProducts($stockActivity);
        return $this;
    }
    
    /**
     * remove existed products not in $data
     * update existed products in $data
     * add new products
     * add queries to Processor
     * 
     * @param StockActivityInterface $stockActivity
     * @param type $data
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\ProductSelectionManagement
     */
    protected function _prepareSetProducts(StockActivityInterface $stockActivity, $data)
    {
        /* load existed products from StockActivity */
        $existedProducts = [];
        $existedProductIds = [];
        $newProducts = $data;
        $stockActivityProducts = $this->getProducts($stockActivity);
        if($stockActivityProducts->getSize()) {
            foreach($stockActivityProducts as $stockActivityProduct) {
                $productId = $stockActivityProduct->getProductId();
                $existedProducts[$productId] = $stockActivityProduct->getData();
                /* remove existed product from $newProducts */
                if(isset($newProducts[$productId])) {
                    $existedProductIds[$productId] = $productId;
                    unset($newProducts[$productId]);
                }
            }
        }
        /* remove existed products but not in post data */
        $deleteProducts = array_diff_key($existedProducts, $data);
        if(count($deleteProducts)) {
            $this->_prepareRemoveProducts($stockActivity, array_keys($deleteProducts));
        }
        
        /* update existed products in the StockActivity */
        $values = $this->_prepateUpdateValues($stockActivityProducts, $data);
        $where = [
            'product_id IN (?)' => $existedProductIds,
            $stockActivity->getResource()->getIdFieldName().'=?' => $stockActivity->getId()
        ];

        if (count($values)) {
            $this->_queryProcessor->addQuery(['type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
                'values' => $values,
                'condition' => $where,
                'table' =>  $stockActivity->getStockActivityProductModel()->getResource()->getMainTable()
            ]);
        }        
        
        /* add new products to the StockActivity */
        $this->_prepareAddNewProducts($stockActivity, $newProducts);

        return $this;
    }

    /**
     * Prepare adding products to StockActivity
     * 
     * @param StockActivityInterface $stockActivity
     * @param array $data
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\ProductSelectionManagement
     */
    protected function _prepareAddProducts(StockActivityInterface $stockActivity, $data)
    {
        /* update existed products in Selection */
        $newProductData = $this->_prepareUpdateExistedProducts($stockActivity, $data);
        /* add new products to Selection */
        $this->_prepareAddNewProducts($stockActivity, $newProductData);

        return $this;
    }

    /**
     * 
     * @param StockActivityInterface $stockActivity
     * @param array $data
     * @return array
     */
    protected function _prepareUpdateExistedProducts(StockActivityInterface $stockActivity, $data)
    {
        $productIds = array_keys($data);
        $stockActivityProducts = $this->getProducts($stockActivity, $productIds);
        /* update existed products in Selection */
        if (!$stockActivityProducts->getSize()) {
            return $data;
        }
        $newProducts = $data;
        $stockActivityProductResource = $stockActivity->getStockActivityProductModel()->getResource();
        $existedProductIds = [];
        foreach ($stockActivityProducts as $stockActivityProduct) {
            $existedProductIds[] = $stockActivityProduct->getProductId();
            /* remove existed products from $data */
            unset($newProducts[$stockActivityProduct->getProductId()]);
        }
        /* prepare updateValues for using in CASE query of Mysql */
        $values = $this->_prepateUpdateValues($stockActivityProducts, $data);
        $where = [
            'product_id IN (?)' => $existedProductIds,
            $stockActivity->getResource()->getIdFieldName().'=?' => $stockActivity->getId()
        ];

        /* add query to the processor */
        if (count($values)) {
            $this->_queryProcessor->addQuery([
                'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
                'values' => $values,
                'condition' => $where,
                'table' => $stockActivityProductResource->getMainTable()
            ]);
        }
        return $newProducts;
    }

    /**
     * 
     * @param StockActivityInterface $stockActivity
     * @param array $data
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\ProductSelectionManagement
     */
    protected function _prepareAddNewProducts(StockActivityInterface $stockActivity, $data)
    {
        /* add new products to Selection */
        if (!count($data)) {
            return $this;
        }
        $stockActivityProductResource = $stockActivity->getStockActivityProductModel()->getResource();
        $insertData = [];
        foreach ($data as $productId => $productData) {
            $productData['product_id'] = $productId;
            $productData[$stockActivity->getResource()->getIdFieldName()] = $stockActivity->getId();
            $insertData[] = $productData;
        }
        /* add query to the processor */
        $this->_queryProcessor->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
            'values' => $insertData,
            'table' => $stockActivityProductResource->getMainTable()
        ]);

        return $this;
    }

    /**
     * Build updateValues for using in CASE query of Mysql
     * 
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $stockActivityProducts
     * @param array $data
     * @return array
     */
    protected function _prepateUpdateValues($stockActivityProducts, $data)
    {
        $updateValues = [];
        $conditions = [];
        $connection = $this->getConnection();
        foreach ($stockActivityProducts as $stockActivityProduct) {
            $productId = $stockActivityProduct->getProductId();            
            if(!isset($data[$productId])) 
                continue;
            $case = $connection->quoteInto('?', $productId);
            /* scan all fields in $data */
            foreach ($data[$productId] as $field => $value) {
                if ($stockActivityProduct->getData($field) != $value) {
                    /* if change the data of $field */
                    $conditions[$field][$case] = $connection->quoteInto('?', $value);
                }
            }
        }
        /* bind conditions to $updateValues */
        foreach ($conditions as $field => $condition) {
            $updateValues[$field] = $connection->getCaseSql('product_id', $condition, $field);
        }
        return $updateValues;
    }

    /**
     * 
     * @param StockActivityInterface $stockActivity
     * @param type $productIds
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\ProductSelectionManagement
     */
    protected function _prepareRemoveProducts(StockActivityInterface $stockActivity, $productIds = [])
    {
        $stockActivityProductResource = $stockActivity->getStockActivityProductModel()->getResource();
        $connection = $this->getConnection();
        $conditions = [$stockActivity->getResource()->getIdFieldName() . ' = ?' => $stockActivity->getId()];
        if (count($productIds)) {
            $conditions['product_id IN (?)'] = $productIds;
        }
        /* add query to Processor */
        $this->_queryProcessor->addQuery(['type' => QueryProcessorInterface::QUERY_TYPE_DELETE,
            'condition' => $conditions,
            'table' => $stockActivityProductResource->getMainTable()
        ]);
        return $this;
    }

}
