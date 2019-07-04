<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magestore\InventorySuccess\Api\Data\Permission\PermissionTypeInterface;
use Magestore\InventorySuccess\Api\Data\Warehouse\WarehouseInterface;
use Magestore\InventorySuccess\Api\Warehouse\WarehouseStockRegistryInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magestore\InventorySuccess\Api\StockActivity\StockActivityInterface;
use Magestore\InventorySuccess\Api\StockActivity\ProductSelectionManagementInterface;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Stock\CollectionFactory as WarehouseStockCollectionFactory;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Shipment\Item\CollectionFactory as WarehouseShipmentItemCollectionFactory;
use Magestore\InventorySuccess\Api\Data\AdjustStock\AdjustStockInterface;

class Warehouse extends \Magento\Framework\Model\AbstractModel
    implements WarehouseInterface, OptionSourceInterface, StockActivityInterface, PermissionTypeInterface
{

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'inventorysuccess_warehouse';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getWarehouse() in this case
     *
     * @var string
     */
    protected $_eventObject = 'warehouse';

    /**
     * @var int
     */
    protected $_permissionType = 1;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var WarehouseStockRegistryInterface
     */
    protected $_warehouseStockRegistryInterface;

    /**
     * @var  \Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_adminSession;

    /**
     * @var bool
     */
    protected $_hasNewManager = false;

    /**
     * @var \Magestore\InventorySuccess\Model\Warehouse\ProductFactory
     */
    protected $_warehouseProductFactory;

    /**
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * Stock Registry
     *
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_datetime;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var \Magestore\InventorySuccess\Api\StockActivity\StockMovementActionInterface
     */
    protected $_stockMovementAction;

    /**
     * @var WarehouseStockCollectionFactory
     */
    protected $warehouseStockCollectionFactory;

    /**
     * @var WarehouseShipmentItemCollectionFactory
     */
    protected $warehouseShipmentItemCollectionFactory;

    /**
     * Warehouse constructor.
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param ResourceModel\Warehouse\Product\CollectionFactory $warehouseProductCollectionFactory
     * @param \Magento\Directory\Model\Region $regionModel
     * @param \Magento\Backend\Model\Auth\Session $adminSession
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param Warehouse\ProductFactory $warehouseProductFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        WarehouseStockRegistryInterface $warehouseStockRegistryInterface,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Backend\Model\Auth\Session $adminSession,
        \Magestore\InventorySuccess\Model\Warehouse\ProductFactory $warehouseProductFactory,
        ProductFactory $productFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        ProductSelectionManagementInterface $productSelectionManagement,
        WarehouseStockCollectionFactory $warehouseStockCollectionFactory,
        WarehouseShipmentItemCollectionFactory $warehouseShipmentItemCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magestore\InventorySuccess\Api\StockActivity\StockMovementActionInterface $stockMovementActionInterface,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->_objectManager = $objectManager;
        $this->_warehouseStockRegistryInterface = $warehouseStockRegistryInterface;
        $this->_regionFactory = $regionFactory;
        $this->_adminSession = $adminSession;
        $this->_warehouseProductFactory = $warehouseProductFactory;
        $this->_productFactory = $productFactory;
        $this->stockRegistry = $stockRegistry;
        $this->_productSelectionManagement = $productSelectionManagement;
        $this->_stockMovementAction = $stockMovementActionInterface;
        $this->warehouseStockCollectionFactory = $warehouseStockCollectionFactory;
        $this->warehouseShipmentItemCollectionFactory = $warehouseShipmentItemCollectionFactory;
        $this->_datetime = $datetime;
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magestore\InventorySuccess\Model\ResourceModel\Warehouse');
    }

    /**
     * @return int
     */
    public function getPermissionType()
    {
        return $this->_permissionType;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $warehouses = $this->getCollection()->getItems();
        $options = [];
        $options[] = [
            'value' => '',
            'label' => __('-- Select a location --')
        ];
        foreach ($warehouses as $warehouse) {
            $options[] = [
                'value' => $warehouse['warehouse_id'],
                'label' => $warehouse['warehouse_name'] . '(' . $warehouse['warehouse_code'] . ')',
            ];
        }
        $this->options = $options;
        return $this->options;
    }


    /**
     * get stockactivity-product model
     *
     * @return StockActivityProductInterface
     */
    public function getStockActivityProductModel()
    {
        return $this->_objectManager->create('Magestore\InventorySuccess\Model\Warehouse\Product');
    }

    /**
     * get warehouse product collection of current warehouse
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection
     */
    public function getWarehouseProductCollection()
    {
        /** @var \Magestore\InventorySuccess\Model\Warehouse\ProductFactory $warehouseProductFactory */
        $warehouseProductCollection = $this->_warehouseProductFactory->create()->getCollection();
        return $warehouseProductCollection->addFieldToFilter('warehouse_id', $this->getId());
    }

    /**
     * get highest qty products
     *
     * @param int $numberProduct
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection
     */
    public function getHighestQtyProducts($numberProduct)
    {
        return $this->warehouseStockCollectionFactory->create()
            ->getHighestQtyProducts($numberProduct, $this->getWarehouseId());
    }

    /**
     * get best seller product
     *
     * @param $numberProduct
     */
    public function getBestSellerProducts($numberProduct)
    {
        $this->warehouseShipmentItemCollectionFactory->create()
            ->getBestSellerProducts($numberProduct, $this->getWarehouseId());
    }

    /**
     * @param \Magento\Framework\Data\Collection $collection
     *
     * @return \Magento\Framework\DataObject
     */
    public function getFirstItem(\Magento\Framework\Data\Collection $collection)
    {
        $collection->setPageSize(1)->setCurPage(1);
        return $collection->getFirstItem();
    }

    /**
     * @return int
     */
    public function checkWarehouseCode($warehouseId = null)
    {
        $collection = $this->getCollection()->addFieldToFilter(self::WAREHOUSE_CODE, $this->getWarehouseCode());
        if ($warehouseId) {
            $collection->addFieldToFilter(
                ['warehouse_id'],
                [
                    ['neq' => $warehouseId],
                ]
            );
        }
        return $collection->count();
    }

    /**
     * @param $productId
     *
     * @return \Magestore\InventorySuccess\Model\Warehouse\Product
     */
    public function getWarehouseProductById($productId)
    {
        $warehouseProductCollection = $this->getWarehouseProductCollection()
            ->addFieldToFilter('product_id', $productId);

        return $this->getFirstItem($warehouseProductCollection);
    }

    /**
     * @param $field
     *
     * @return mixed
     */
    public function getTitleByField($field)
    {
        $fieldArray = [
            'name' => __('Location Name'),
            'manager_name' => __('Manager\'s Name'),
            'manager_email' => __('Manager\'s Email'),
            'telephone' => __('Telephone'),
            'street' => __('Street'),
            'city' => __('City'),
            'country_id' => __('Country'),
            'stateEl' => __('State/Province'),
            'state' => __('State/Province'),
            'state_id' => __('State/Province'),
            'postcode' => __('Zip/Postal Code'),
            'status' => __('Status'),
        ];
        if (!$fieldArray[$field]) {
            return $field;
        }

        return $fieldArray[$field];
    }

    /**
     * add product inline from none warehouse product
     *
     * @param array $postData
     * @return array
     */
    public function addProductsInline($postData = [])
    {
        foreach ($postData as $warehouseId => $productIds) {
            $this->createAdjustment($warehouseId, $productIds);
        }
        return $this;
    }

    /**
     * create adjustment from warehouse id and warehouse ids and qty
     *
     * @param int $warehouseId
     * @param array $productIds
     * @return $this
     */
    public function createAdjustment($warehouseId, $productIds)
    {
        $adjustData = $this->prepareNoneWarehouseProductData($productIds);
        if (count($adjustData) > 0) {
            $adjustData = $this->_prepareAdjustmentData($adjustData, $warehouseId);
            $this->getStockActivityProductModel()->createAdjustment($adjustData, false);
        }
        return $this;
    }

    /**
     * Prepare stock adjustment data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $data
     * @return array
     */
    protected function _prepareAdjustmentData($adjustData, $warehouseId)
    {
        $adjustData[AdjustStockInterface::WAREHOUSE_ID] = $warehouseId;
        $adjustData[AdjustStockInterface::WAREHOUSE_NAME] = null;
        $adjustData[AdjustStockInterface::WAREHOUSE_CODE] = null;
        $adjustData[AdjustStockInterface::REASON] = __('Add None Location Product');
        return $adjustData;
    }

    public function prepareNoneWarehouseProductData($productIds)
    {
        $adjustData = [];
        $products = $this->_objectManager
            ->create('Magestore\InventorySuccess\Model\ResourceModel\Product\NoneInWarehouse\Collection');
//            ->addFieldToFilter('entity_id', ['in' => $productIds]);
        foreach ($products as $product) {
            if (in_array($product->getId(), array_values($productIds))) {
                $adjustData['products'][$product->getId()] = [
                    'adjust_qty' => $product->getQty(),
                    'change_qty' => $product->getQty(), 
                    'old_qty' => 0,
                    'product_sku' => $product->getSku(),
                    'product_name' => $product->getName()
                ];
            }
        }
        return $adjustData;
    }

    /**
     * Prepare warehouse product data from stock item
     *
     * @param $productId
     * @param \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem
     * @return array
     */
    protected function _prepareWarehouseProductDataFromStockItem($productId, \Magento\CatalogInventory\Api\Data\StockItemInterface $stockItem)
    {
        return [
            'warehouse_id' => $this->getWarehouseId(),
            'product_id' => $productId,
            'total_qty' => $stockItem->getQty()
        ];
    }

    /**
     * @param $productId
     * @param $data
     * @return \Magestore\InventorySuccess\Api\Data\StockMovement\StockMovementInterface
     */
    protected function prepareStockMovementData($productId, $data)
    {
        $stockMovementData = [
            'product_id' => $productId,
            'product_sku' => $this->_productFactory->create()->load($productId)->getSku(),
            'qty' => $data['total_qty'],
            'action_code' => \Magestore\InventorySuccess\Model\StockMovement\Options\AddProductStockMovementMask::STOCK_MOVEMENT_ACTION_CODE,
            'warehouse_id' => $this->getWarehouseId()
        ];
        return $stockMovementData;
    }

    /**
     * Get array products from a warehouse
     *
     * @param array $productIds
     * @return array
     */
    public function getArrayProducts($productIds = array())
    {
        $list = array();
        $products = $this->getProducts($productIds);
        if (count($products)) {
            foreach ($products as $product) {
                $list[$product->getProductId()] = $product;
            }
        }
        return $list;
    }

    /**
     * Get products in an adjust
     *
     * @param array $productIds
     * @return \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\Collection[]
     */
    public function getProducts($productIds = array())
    {
        return $this->_warehouseStockRegistryInterface->getStocks($this->getWarehouseId(), $productIds);
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getPrimaryWarehouse()
    {
        $warehouse = $this->getCollection()->addFieldToFilter('is_primary', 1);
        return $warehouse->getFirstItem();
    }

    /**
     * Warehouse id
     *
     * @return int|null
     */
    public function getWarehouseId()
    {
        return $this->_getData(self::WAREHOUSE_ID);
    }

    /**
     * Set warehouse id
     *
     * @param int $warehouseId
     * @return $this
     */
    public function setWarehouseId($warehouseId)
    {
        return $this->setData(self::WAREHOUSE_ID, $warehouseId);
    }

    /**
     * Warehouse name
     *
     * @return string
     */
    public function getWarehouseName()
    {
        return $this->_getData(self::WAREHOUSE_NAME);
    }

    /**
     * Set warehouse name
     *
     * @param int $warehouseName
     * @return $this
     */
    public function setWarehouseName($warehouseName)
    {
        return $this->setData(self::WAREHOUSE_NAME, $warehouseName);
    }

    /**
     * Warehouse code
     *
     * @return string
     */
    public function getWarehouseCode()
    {
        return $this->_getData(self::WAREHOUSE_CODE);
    }

    /**
     * Set warehouse code
     *
     * @param int $warehouseCode
     * @return $this
     */
    public function setWarehouseCode($warehouseCode)
    {
        return $this->setData(self::WAREHOUSE_CODE, $warehouseCode);
    }

    /**
     * Contact email
     *
     * @return string|null
     */
    public function getContactEmail()
    {
        return $this->_getData(self::CONTACT_EMAIL);
    }

    /**
     * Set contact email
     *
     * @param int $contactEmail
     * @return $this
     */
    public function setContactEmail($contactEmail)
    {
        return $this->setData(self::CONTACT_EMAIL, $contactEmail);
    }

    /**
     * telephone
     *
     * @return int|null
     */
    public function getTelephone()
    {
        return $this->_getData(self::TELEPHONE);
    }

    /**
     * Set telephone
     *
     * @param int $telephone
     * @return $this
     */
    public function setTelephone($telephone)
    {
        return $this->setData(self::TELEPHONE, $telephone);
    }

    /**
     * Street
     *
     * @return string|null
     */
    public function getStreet()
    {
        return $this->_getData(self::STREET);
    }

    /**
     * Set street
     *
     * @param int $street
     * @return $this
     */
    public function setStreet($street)
    {
        return $this->setData(self::STREET, $street);
    }

    /**
     * City
     *
     * @return string|null
     */
    public function getCity()
    {
        return $this->_getData(self::CITY);
    }

    /**
     * Set city
     *
     * @param int $city
     * @return $this
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * Country id
     *
     * @return string|null
     */
    public function getCountryId()
    {
        return $this->_getData(self::COUNTRY_ID);
    }

    /**
     * Set country id
     *
     * @param int $countryId
     * @return $this
     */
    public function setCountryId($countryId)
    {
        return $this->setData(self::COUNTRY_ID, $countryId);
    }

    /**
     * Region
     *
     * @return string|null
     */
    public function getRegion()
    {
        return $this->_getData(self::REGION);
    }

    /**
     * Set region
     *
     * @param int $region
     * @return $this
     */
    public function setRegion($region)
    {
        return $this->setData(self::REGION, $region);
    }

    /**
     * Region ID
     *
     * @return int|null
     */
    public function getRegionId()
    {
        return $this->_getData(self::REGION_ID);
    }

    /**
     * Set region id
     *
     * @param int $regionId
     * @return $this
     */
    public function setRegionId($regionId)
    {
        return $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * Postcode
     *
     * @return string|null
     */
    public function getPostcode()
    {
        return $this->_getData(self::POSTCODE);
    }

    /**
     * Set postcode
     *
     * @param int $postcode
     * @return $this
     */
    public function setPostcode($postcode)
    {
        return $this->setData(self::POSTCODE, $postcode);
    }

    /**
     * Status
     *
     * @return int|null
     */
    public function getStatus()
    {
        return $this->_getData(self::STATUS);
    }

    /**
     * Set status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Is primary
     *
     * @return boolean
     */
    public function getIsPrimary()
    {
        return $this->_getData(self::IS_PRIMARY);
    }

    /**
     * Set is primary
     *
     * @param int $isPrimary
     * @return $this
     */
    public function setIsPrimary($isPrimary)
    {
        return $this->setData(self::IS_PRIMARY, $isPrimary);
    }

    /**
     * Created at
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_getData(self::CREATED_AT);
    }

    /**
     * Set created at
     *
     * @param int $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Updated at
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_getData(self::UPDATED_AT);
    }

    /**
     * Set updated at
     *
     * @param int $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * get Store Id
     *
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->_getData(self::STORE_ID);
    }

    /**
     * Set store id
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }
}