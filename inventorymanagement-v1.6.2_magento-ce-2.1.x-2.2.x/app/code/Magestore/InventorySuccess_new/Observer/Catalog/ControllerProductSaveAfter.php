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

class ControllerProductSaveAfter implements ObserverInterface
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
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magestore\InventorySuccess\Api\AdjustStock\AdjustStockManagementInterface $adjustStockManagement
     * @param \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory
     * @param \Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface $warehouseStockRegistry
     * @param \Magestore\InventorySuccess\Api\StockActivity\StockChangeInterface $stockChange
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
        StockConfigurationInterface $stockConfiguration
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
    }

    /**
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $this->_updateStockItem($product);

        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            /* do not update catalog qty if create new configurable product */
            $this->updateCatalog = false;
            /* process add/ edit configurable product */
            $this->processConfigurableProduct($product);
            /* still allow to add configurable product to warehouse from 01/25/2017 */
            //return $this;
        }
        /* process add/ edit product */
        $postData = $this->_request->getParam('product');
        $warehouseStockData = isset($postData[WarehouseStock::WAREHOUSE_STOCK_FIELDS][WarehouseStock::WAREHOUSE_STOCK_LISTING]) ?
            $postData[WarehouseStock::WAREHOUSE_STOCK_FIELDS][WarehouseStock::WAREHOUSE_STOCK_LISTING] : [];

        $ForcewarehouseStockData = isset($postData['warehouse_stock_edit'][WarehouseStock::WAREHOUSE_STOCK_LISTING]) ?
            $postData['warehouse_stock_edit'][WarehouseStock::WAREHOUSE_STOCK_LISTING] : [];

        if (isset($postData['quantity_and_stock_status']['use_config_qty'])
            && $postData['quantity_and_stock_status']['use_config_qty']
        ) {
            $this->updateCatalog = false;
        }

        if ($ForcewarehouseStockData) {
            $this->processUpdateWarehouseStock($product, $ForcewarehouseStockData);
        }
        if (!count($warehouseStockData)) {
            return;
        }
        $this->processUpdateWarehouseStock($product, $warehouseStockData);
    }

    /**
     * Adjust stock of product in Locations
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $warehouseStockData
     */
    protected function processUpdateWarehouseStock($product, $warehouseStockData)
    {
        $updateWarehouseIds = [];
        foreach ($warehouseStockData as $stockItemData) {
            if($product->isComposite()){
                if(isset($stockItemData['delete']) && isset($stockItemData['warehouse_id'])){
                    $this->deleteItem($product->getId(),$stockItemData['warehouse_id']);
                    continue;
                }
            }

            $adjustData = $this->_prepareAdjustmentData($product, $stockItemData);
            $shelfLocationData = $this->_prepareShelfLocationData($product, $stockItemData);
            $warehouseId = $adjustData[AdjustStockInterface::WAREHOUSE_ID];

            $checkForedit = $this->_checkForceEdit($stockItemData);

            /* continue if the warehouse has been processed */
            if (in_array($warehouseId, $updateWarehouseIds)) {
                continue;
            }
            $updateWarehouseIds[] = $warehouseId;

            if ($checkForedit) {
                $forceEdit = $this->_prepareForceEditData($product, $stockItemData);
                $this->forceEdit($forceEdit);
            } else {
                /* create stock adjustment */
                $this->_createAdjustment($adjustData);
            }
            /* update shelf location of in warehouse */
            $this->_massUpdateShelfLocation($shelfLocationData);
        }

    }

    /**
     *
     */
    protected function deleteItem($productId, $warehouseId){
        if(isset($productId) && isset($warehouseId)) {
            $this->warehouseStockRegistry->removeProduct($warehouseId, $productId);
        }
    }

    /**
     *
     */
    protected function forceEdit($forceEdit)
    {
        if (!count($forceEdit)) {
            return;
        }
        /** @var array $locations [$productId => $shelfLocation] */
        foreach ($forceEdit as $warehouseId => $avaiQty) {
            if (!count($avaiQty)) {
                continue;
            }
            $this->warehouseStockRegistry->forceEditAvailableQty($warehouseId, $avaiQty);
        }
    }

    protected function _prepareForceEditData($product, $data)
    {
        $forceEdit = [];
        $availableQty = isset($data['available_qty']) ? $data['available_qty'] : null;
        $warehouseId = isset($data['warehouse_id']) ? $data['warehouse_id'] : $data['warehouse_select'];
        if (!$availableQty || !$warehouseId) {
            return [];
        }
        $forceEdit[$warehouseId] = [$product->getId() => $availableQty];
        return $forceEdit;
    }

    /**
     * @param $data
     * @return bool
     */
    protected function _checkForceEdit($data)
    {
        if (isset($data['use_config_available_qty']) && $data['use_config_available_qty']) {
            return true;
        }
        return false;
    }

    /**
     * Process to update stock of childs of configurable product in Warehouse
     *
     * @param \Magento\Catalog\Model\Product $product
     */
    protected function processConfigurableProduct($product)
    {
        $postData = $this->_request->getParams();
        if ((!isset($postData['configurable-matrix']) || !count($postData['configurable-matrix']))
            && !isset($postData['product']['configurable-matrix-serialized'])
        ) {
            return;
        }
        if (isset($postData['configurable-matrix']) && count($postData['configurable-matrix'])) {
            $productChildrent = $postData['configurable-matrix'];
        } elseif (isset($postData['product']['configurable-matrix-serialized'])) {
            $productChildrent = json_decode($postData['product']['configurable-matrix-serialized'], true);
        }

        $registerWarehousesBySku = [];

        if (count($productChildrent)) {
            foreach ($productChildrent as $childData) {
                if (!isset($childData['canEdit']) || !$childData['canEdit']) {
                    /* can not edit data of this child */
                    continue;
                }
                $warehouseId = isset($childData['register_warehouse']) ? $childData['register_warehouse'] : 0;
                if (!$warehouseId) {
                    /* there is no registration warehouse */
                    continue;
                }
                $qty = isset($childData['qty']) ? $childData['qty'] : 0;
                $shelfLocation = isset($childData['shelf_location']) ? $childData['shelf_location'] : null;
                $registerWarehousesBySku[$childData['sku']] = [
                    'warehouse_id' => $warehouseId,
                    'total_qty' => $qty,
                    'shelf_location' => $shelfLocation,
                ];
            }
        }

        $childProducts = $this->productCollectionFactory->create()
            ->addFieldToFilter('sku', ['in' => array_keys($registerWarehousesBySku)])
            ->addAttributeToSelect('name');
        if (!$childProducts->getSize()) {
            return;
        }
        $stockByWarehouse = [];
        $shelfLocations = [];
        foreach ($childProducts as $childProduct) {
            if ($childProduct->getTypeId() == \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL) {
                continue;
            }
            $warehouseStockData = $registerWarehousesBySku[$childProduct->getSku()];
            $warehouseId = $warehouseStockData['warehouse_id'];
            $adjustData = $this->_prepareAdjustmentData($childProduct, $warehouseStockData);
            /* merge adjust data to $stockByWarehouse */
            if (!isset($stockByWarehouse[$warehouseId])) {
                $stockByWarehouse[$warehouseId] = $adjustData;
            } else {
                $stockByWarehouse[$warehouseId]['products'][$childProduct->getId()] = $adjustData['products'][$childProduct->getId()];
            }
            /* merge location data to $shelfLocations */
            $location = $this->_prepareShelfLocationData($childProduct, $warehouseStockData);
            if (isset($location[$warehouseId][$childProduct->getId()])) {
                $shelfLocations[$warehouseId][$childProduct->getId()] = $location[$warehouseId][$childProduct->getId()];
            }
        }

        /* process adjust stock in warehouses */
        if (count($stockByWarehouse)) {
            foreach ($stockByWarehouse as $warehouseStock) {
                $this->_createAdjustment($warehouseStock);
            }
        }

        /* process update shelf locations in warehouses */
        $this->_massUpdateShelfLocation($shelfLocations);
    }

    /**
     * Create stock adjustment, $adjustData['products' => [], 'warehouse_id' => $warehouseId,... ]
     *
     * @param array $adjustData
     * @return \Magestore\InventorySuccess\Model\AdjustStock
     */
    protected function _createAdjustment($adjustData)
    {
        $adjustStock = $this->_adjustStockFactory->create();

        /* create stock adjustment, require products, require qty changed */
        $this->_adjustStockManagement->createAdjustment($adjustStock, $adjustData, true, true);

        /* created adjuststock or not */
        if ($adjustStock->getId()) {
            /* complete stock adjustment */
            $this->_adjustStockManagement->complete($adjustStock, $this->updateCatalog);
        }
        return $adjustStock;
    }

    /**
     * Add product to Warehouse
     *
     * @param int $product
     * @param int $warehouseId
     * @param float $qty
     */
    protected function _addProductToWarehouse($product, $warehouseId, $qty)
    {
        $this->stockChange->update($warehouseId, $product->getId(), $qty);
    }

    /**
     * Prepare stock adjustment data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $data
     * @return array
     */
    protected function _prepareAdjustmentData($product, $data)
    {
        $adjustData = [];

        $totalQty = isset($data['total_qty']) ? $data['total_qty'] : 0;
        $adjustData['products'] = [$product->getId() => [
            'adjust_qty' => $totalQty,
            'product_name' => $product->getName(),
            'product_sku' => $product->getSku(),
        ]];

        $adjustData[AdjustStockInterface::WAREHOUSE_ID] = isset($data['warehouse_id']) ?
            $data['warehouse_id'] :
            $data['warehouse_select'];
        $adjustData[AdjustStockInterface::WAREHOUSE_CODE] = isset($data['warehouse_code']) ?
            $data['warehouse_code'] :
            null;
        $adjustData[AdjustStockInterface::WAREHOUSE_NAME] = isset($data['warehouse_name']) ?
            $data['warehouse_name'] :
            null;
        $adjustData[AdjustStockInterface::REASON] = __('Direct Adjust from Product edit');

        return $adjustData;
    }

    /**
     * Prepare shelf location data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $data
     * @return array
     */
    protected function _prepareShelfLocationData($product, $data)
    {
        $locations = [];
        $shelfLocation = isset($data['shelf_location']) ? $data['shelf_location'] : null;
        $warehouseId = isset($data['warehouse_id']) ? $data['warehouse_id'] : $data['warehouse_select'];
        if (!$shelfLocation || !$warehouseId) {
            return [];
        }
        $locations[$warehouseId] = [$product->getId() => $shelfLocation];
        return $locations;
    }

    /**
     * Mass update shelf location of products in warehouses
     * $shelfLocationData[$warehouseId => [$productId => $shelfLocation]]
     *
     * @param array $shelfLocationData
     */
    protected function _massUpdateShelfLocation($shelfLocationData)
    {
        if (!count($shelfLocationData)) {
            return;
        }
        /** @var array $locations [$productId => $shelfLocation] */
        foreach ($shelfLocationData as $warehouseId => $locations) {
            if (!count($locations)) {
                continue;
            }
            $this->warehouseStockRegistry->updateLocation($warehouseId, $locations);
        }
    }

    /**
     *
     * @param \Magento\Catalog\Model\Product $product
     */
    protected function _updateStockItem($product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId());
        if (!$stockItem) {
            return;
        }
        if ($stockItem->getData('website_id') != $this->stockConfiguration->getDefaultScopeId()) {
            return;
        }
        $stockItemData = $this->_prepareSaveDataStockItem($stockItem);
        $this->warehouseStockRegistry->cloneStockItemData($product->getId(), $stockItemData, array(), array($this->stockConfiguration->getDefaultScopeId()));

        $stockStatusData = array('stock_status' => $stockItem->getIsInStock());
        $this->warehouseStockRegistry->cloneStockStatus($product->getId(), $stockStatusData, array(), array($this->stockConfiguration->getDefaultScopeId()));
    }

    /**
     *
     * @param \Magento\CatalogInventory\Model\Stock\Item $item
     * @return array
     */
    protected function _prepareSaveDataStockItem($item)
    {
        $stockItemData = array();
        $updateFields = array(
            'min_qty',
            'use_config_min_qty',
            'is_qty_decimal',
            'is_decimal_divided',
            'backorders',
            'use_config_backorders',
            'min_sale_qty',
            'use_config_min_sale_qty',
            'max_sale_qty',
            'use_config_max_sale_qty',
            'is_in_stock',
            'use_config_notify_stock_qty',
            'manage_stock',
            'use_config_manage_stock',
            'use_config_qty_increments',
            'use_config_enable_qty_inc',
            'enable_qty_increments',
            'qty_increments',
        );
        foreach ($updateFields as $field) {
            $stockItemData[$field] = $item->getData($field);
        }
        return $stockItemData;
    }
}