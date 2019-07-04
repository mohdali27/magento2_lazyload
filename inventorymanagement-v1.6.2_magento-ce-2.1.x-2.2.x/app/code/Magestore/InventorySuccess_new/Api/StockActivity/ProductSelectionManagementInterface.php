<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\StockActivity;


interface ProductSelectionManagementInterface
{
    /**
     * Get linked products
     * 
     * @param \Magestore\InventorySuccess\Api\StockActivity\StockActivityInterface $stockActivity
     * @param array $productIds
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getProducts(StockActivityInterface $stockActivity, $productIds = []);
    
    /**
     * Get linked Product
     * 
     * @param \Magestore\InventorySuccess\Api\StockActivity\StockActivityInterface $stockActivity
     * @param int $productId
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function getProduct(StockActivityInterface $stockActivity, $productId);
    
    /**
     * Add product to Selection
     * 
     * @param \Magestore\InventorySuccess\Api\StockActivity\StockActivityInterface $stockActivity
     * @param int $productId
     * @param array $data
     * @return ProductSelectionManagementInterface
     */
    public function addProduct(StockActivityInterface $stockActivity, $productId, $data);
    
    /**
     * Add products to Selection
     * 
     * @param \Magestore\InventorySuccess\Api\StockActivity\StockActivityInterface $stockActivity
     * @param array $data
     * @return ProductSelectionManagementInterface
     */
    public function addProducts(StockActivityInterface $stockActivity, $data);
    
    /**
     * Remove product from Selection
     * 
     * @param \Magestore\InventorySuccess\Api\StockActivity\StockActivityInterface $stockActivity
     * @param int $productId
     * @return ProductSelectionManagementInterface
     */
    public function removeProduct(StockActivityInterface $stockActivity, $productId);
    
    /**
     * Remove products from Selection
     * 
     * @param \Magestore\InventorySuccess\Api\StockActivity\StockActivityInterface $stockActivity
     * @param array $productIds
     * @return ProductSelectionManagementInterface
     */
    public function removeProducts(StockActivityInterface $stockActivity, $productIds); 
    
    /**
     * Remove all products from Selection
     * 
     * @param \Magestore\InventorySuccess\Api\StockActivity\StockActivityInterface $stockActivity
     * @return ProductSelectionManagementInterface
     */
    public function removeAllProducts(StockActivityInterface $stockActivity);    
    
    /**
     * Set products to Selection
     * 
     * @param \Magestore\InventorySuccess\Api\StockActivity\StockActivityInterface $stockActivity
     * @param array $data
     * @return ProductSelectionManagementInterface
     */
    public function setProducts(StockActivityInterface $stockActivity, $data);    
    
}