<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;


class ProductStockList extends ProductDataProvider
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;
    /** 
     * @var  \Magestore\InventorySuccess\Model\AdjustStockFactory
     */
    protected $adjustStockFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magestore\InventorySuccess\Model\Warehouse\WarehouseManagement $warehouseManagement
     * @param \Magento\Framework\App\RequestInterface request
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
        \Magestore\InventorySuccess\Model\AdjustStockFactory $adjustStockFactory,
        \Magento\Framework\App\RequestInterface $request,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
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
        $this->adjustStockFactory = $adjustStockFactory;
        $this->collection = $this->getProductCollection();
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
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
            if(!isset($item['adjust_qty'])) {
                $item['adjust_qty'] = 0;
            }
            if(!isset($item['stocktaking_qty'])) {
                $item['stocktaking_qty'] = 0;
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
        return $collection;
    }

    /**
     * Get stocktaking warehouse id
     *
     * @return string
     */
    public function getWarehouseId()
    {
        $adjuststockId = $this->request->getParam('adjuststock_id');
        $warehouseId = 0;
        if($adjuststockId){
            $adjustStock = $this->adjustStockFactory->create()->load($adjuststockId);
            $warehouseId = $adjustStock->getWarehouseId();
        }
        if($this->request->getParam('warehouse_id')){
            $warehouseId = $this->request->getParam('warehouse_id');
        }
       
        return $warehouseId;
    }

}