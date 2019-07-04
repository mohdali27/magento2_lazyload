<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock;

use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magestore\InventorySuccess\Model\ResourceModel\TransferStock;
use Magestore\InventorySuccess\Model\TransferStock as TransferStockModel;

/**
 * Class Generate
 * @package Magestore\InventorySuccess\Ui\DataProvider\AdjustStock\Form
 */
class WarehouseProductStockList extends ProductDataProvider
{

    const MAPPING_FIELD = [
        'total_qty' => 'warehouse_product.total_qty',
        'qty_to_ship' => 'warehouse_product.total_qty - warehouse_product.qty',
        'sum_total_qty' => 'SUM(warehouse_product.total_qty)',
        'sum_qty_to_ship' => 'SUM(warehouse_product.total_qty - warehouse_product.qty)',
        'available_qty' => 'SUM(warehouse_product.qty)',
        'total_qty_shipped' => 'SUM(warehouse_shipment_item.qty)',
    ];
    /**
     * @var \Magestore\InventorySuccess\Model\Warehouse\WarehouseManagement
     */
    protected $warehouseManagement;


    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * GlobalStock collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /** @var  \Magestore\InventorySuccess\Model\TransferStockFactory */
    protected $transferStockFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magestore\InventorySuccess\Model\Warehouse\WarehouseManagement $warehouseManagement
     * @param \Magento\Framework\App\RequestInterface request
     * @param \Magestore\InventorySuccess\Model\ResourceModel\AdjustStock $adjustStockResource
     * @param \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory
     * @param \Magento\Ui\DataProvider\AddFieldToCollectionInterface[] $addFieldStrategies
     * @param \Magento\Ui\DataProvider\AddFilterToCollectionInterface[] $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magestore\InventorySuccess\Model\Warehouse\WarehouseManagement $warehouseManagement,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\InventorySuccess\Model\TransferStockFactory $transferStockFactory,
        \Magestore\InventorySuccess\Model\Locator\LocatorFactory $locatorFactory,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    )
    {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $collectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );
        $this->request = $request;
        $this->warehouseManagement = $warehouseManagement;
        $this->transferStockFactory = $transferStockFactory;
        $this->collection = $this->getProductCollection();

    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        $items = $this->getCollection()->toArray();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $path = $storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        foreach ($items as &$item) {
            if(isset($item['image'])) {
                $item['image_url'] = $path.'catalog/product'.$item['image'];
            }
            if(isset($item['available_qty']) && (floatval($item['available_qty']) <= 0)) {
               $item['qty'] = 0;
            }
        }

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCollection()
    {
        $warehouseId = $this->getWarehouseId();
      
        if ($warehouseId) {
            $collection = $this->warehouseManagement->getListProduct($warehouseId);
        } else {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $collection = $objectManager->create('Magestore\InventorySuccess\Model\ResourceModel\TransferStock\GlobalStock\Collection');
        }

        $sorting = $this->request->getParam('sorting');
        if(isset($sorting) && !empty($sorting['field'])
            && !empty($sorting['direction'])){
            if(array_key_exists($sorting['field'],self::MAPPING_FIELD)){
                $collection->getSelect()->order(new \Zend_Db_Expr(self::MAPPING_FIELD[$sorting['field']] . $sorting['direction']));
            }
        }
        return $collection;
    }

    /**
     * @return int|mixed
     */
    public function getWarehouseId()
    {
        $warehouseId = 0;
        if($this->request->getParam('warehouse_label_id')){
            $warehouseId = $this->request->getParam('warehouse_label_id');
        }      
        return $warehouseId;
    }

    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if (in_array($filter->getField(), ['category'])) {
            $value = $filter->getValue();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $collection1 = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
                ->joinField(
                    'category_id',
                    'catalog_category_product',
                    'category_id',
                    'product_id=entity_id',
                    'category_id=' . $value,
                    'left'
                );
            $collection1->getSelect()->where('category_id = ?', $value);
            $productIds = $collection1->getColumnValues('entity_id');
            $this->getCollection()->getSelect()->where('`e`.entity_id in (?)', $productIds);
        } else {
            return parent::addFilter($filter);
        }
    }

}