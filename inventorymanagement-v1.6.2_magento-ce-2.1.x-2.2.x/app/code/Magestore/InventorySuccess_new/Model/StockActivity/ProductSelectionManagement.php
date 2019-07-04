<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\StockActivity;

use Magestore\InventorySuccess\Api\StockActivity\ProductSelectionManagementInterface;
use Magestore\InventorySuccess\Api\StockActivity\StockActivityInterface;
use Magestore\InventorySuccess\Api\Helper\SystemInterface;

class ProductSelectionManagement implements ProductSelectionManagementInterface
{

    /**
     *
     * @var \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\ProductSelectionManagementFactory
     */
    protected $_resourceProductSelectionManagementFactory;
    
    /**
     *
     * @var \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface
     */
    protected $_stockChange;  
    
    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface
     */
    protected $_warehouseStockRegsitry;    
    
    /**
     * @var \Magestore\InventorySuccess\Model\WarehouseFactory
     */
    protected $_warehouseFactory;       
    
    /**
     * @var \Magestore\InventorySuccess\Api\IncrementIdManagementInterface
     */
    protected $_incrementIdManagement;
    
    /**
     * @var \Magestore\InventorySuccess\Api\Helper\SystemInterface
     */
    protected $_systemHelper;

    /**
     * 
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\ProductSelectionManagementFactory $resourceProductSelectionManagementFactory,
        \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface $stockChange,
        \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegsitry,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        \Magestore\InventorySuccess\Api\IncrementIdManagementInterface $incrementIdManagement,    
        \Magestore\InventorySuccess\Api\Helper\SystemInterface $systemHelper
    )
    {
        $this->_resourceProductSelectionManagementFactory = $resourceProductSelectionManagementFactory;
        $this->_stockChange = $stockChange;
        $this->_warehouseStockRegsitry = $warehouseStockRegsitry;
        $this->_warehouseFactory = $warehouseFactory;
        $this->_incrementIdManagement = $incrementIdManagement;
        $this->_systemHelper = $systemHelper;
    }
    
    /**
     * Create new Product Selection
     * 
     * @param StockActivityInterface $stockActivity
     * @param type $data
     * @return StockActivityInterface
     */
    public function createSelection(StockActivityInterface $stockActivity, $data)
    {
        $stockActivity->getResource()->save($stockActivity);
        if(isset($data['products'])) {
            $this->setProducts($stockActivity, $data['products']);
        }
        return $stockActivity;
    }
    
    /**
     * Generate an unique code for Selection
     * 
     * @param string $selectionCode
     * @return string
     */
    public function generateUniqueCode($selectionCode)
    {
        return $this->_incrementIdManagement->getNextCode($selectionCode);
    }

    /**
     * @inheritdoc
     */
    public function addProduct(StockActivityInterface $stockActivity, $productId, $data)
    {
        $this->getResource()->addProducts($stockActivity, [$productId => $data]);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addProducts(StockActivityInterface $stockActivity, $data)
    {
        $this->getResource()->addProducts($stockActivity, $data);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getProduct(StockActivityInterface $stockActivity, $productId)
    {
        $stockActivityResource = $stockActivity->getResource();
        $collection = $stockActivity->getStockActivityProductModel()->getCollection();

        $stockActivityProduct = $collection->addFieldToFilter($stockActivityResource->getIdFieldName(), $stockActivity->getId())
                ->addFieldToFilter('product_id', $productId)
                ->setPageSize(1)->setCurPage(1)
                ->getFirstItem();

        return $stockActivityProduct;
    }

    /**
     * @inheritdoc
     */
    public function getProducts(StockActivityInterface $stockActivity, $productIds = [])
    {
        return $this->getResource()->getProducts($stockActivity);
    }

    /**
     * @inheritdoc
     */
    public function removeProduct(StockActivityInterface $stockActivity, $productId)
    {
        $this->getResource()->removeProducts($stockActivity, [$productId]);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeProducts(StockActivityInterface $stockActivity, $productIds)
    {
        $this->getResource()->removeProducts($stockActivity, $productIds);
        return $this;
    }
    
    /**
     * @inheritdoc
     */    
    public function removeAllProducts(StockActivityInterface $stockActivity)
    {
        $this->getResource()->removeAllProducts($stockActivity);
        return $this;        
    }    

    /**
     * @inheritdoc
     */
    public function setProducts(StockActivityInterface $stockActivity, $data)
    {
        $this->getResource()->setProducts($stockActivity, $data);
        return $this;
    }
    

    /**
     * Get resource model
     * 
     * @return Magestore\InventorySuccess\Model\ResourceModel\StockActivity\ProductSelectionManagement
     */
    public function getResource()
    {
        return $this->_resourceProductSelectionManagementFactory->create();
    }
    
    /**
     * Get resource model of StockActivity
     * 
     * @param StockActivityInterface $stockActivity
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function getStockActivityResource(StockActivityInterface $stockActivity)
    {
        return $stockActivity->getResource();
    }

}
