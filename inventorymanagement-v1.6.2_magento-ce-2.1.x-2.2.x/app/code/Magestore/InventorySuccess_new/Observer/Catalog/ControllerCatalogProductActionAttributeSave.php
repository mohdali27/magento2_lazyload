<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Catalog;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magestore\InventorySuccess\Ui\DataProvider\Product\Form\Modifier\WarehouseStock;
use Magestore\InventorySuccess\Api\Data\AdjustStock\AdjustStockInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

class ControllerCatalogProductActionAttributeSave implements ObserverInterface
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magestore\InventorySuccess\Model\AdjustStockFactory
     */
    protected $_adjustStockFactory;

    /**
     * @var \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface
     */
    protected $_adjustStockManagement;

    /**
     * @var \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface
     */
    protected $warehouseStockRegistry;

    /**
     * @var \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface
     */
    protected $stockChange;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var bool
     */
    protected $updateCatalog = true;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;
    /**
     * @var \Magento\Catalog\Helper\Product\Edit\Action\Attribute
     */
    protected $attributeHelper;

    /**
     * ControllerCatalogProductActionAttributeSave constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface $adjustStockManagement
     * @param \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory
     * @param \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegistry
     * @param \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface $stockChange
     * @param StockRegistryInterface $stockRegistry
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param StockConfigurationInterface $stockConfiguration
     * @param \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface $adjustStockManagement,
        \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory,
        \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegistry,
        \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface $stockChange,
        StockRegistryInterface $stockRegistry,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        StockConfigurationInterface $stockConfiguration,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attributeHelper
    )
    {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $coreRegistry;
        $this->_request = $request;
        $this->_adjustStockManagement = $adjustStockManagement;
        $this->_adjustStockFactory = $adjustStockFactory;
        $this->warehouseStockRegistry = $warehouseStockRegistry;
        $this->stockChange = $stockChange;
        $this->stockRegistry = $stockRegistry;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->stockConfiguration = $stockConfiguration;
        $this->attributeHelper = $attributeHelper;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer)
    {
        $params = $observer->getRequest()->getParams();
        $inventoryData = isset($params['inventory']) ? $params['inventory'] : [];
        $productIds = $this->attributeHelper->getProductIds();
        if($inventoryData) {
            foreach ($productIds as $id) {
                $this->warehouseStockRegistry->cloneStockItemData($id, $inventoryData, array(), array());
            }
        }
    }
}