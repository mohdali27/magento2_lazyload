<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\SupplyNeeds\DataForm;

use Magento\Ui\DataProvider\AbstractDataProvider;
//use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Magestore\InventorySuccess\Model\ResourceModel\SupplyNeeds\Product\CollectionFactory;
use Magento\Framework\UrlInterface;

/**
 * Class DataProvider
 */
class ProductDataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $pageCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        \Magento\Framework\App\RequestInterface $request,
        \Magestore\InventorySuccess\Model\SupplyNeeds\SupplyNeedsManagement $supplyNeedsManagement,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->_supplyNeedsManagement = $supplyNeedsManagement;
//        $this->collection = $collectionFactory->create();
        /** @var \Magestore\InventorySuccess\Model\Locator\Locator $locator */
        $locator = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\Locator\LocatorFactory'
        )->create();
        $supplyNeedsTopFilter = $locator->getSesionByKey('supply_needs_top_filter');
        $topFilter = $supplyNeedsTopFilter['top_filter'];
        if (!$topFilter) {
            $this->collection = $collectionFactory->create()->addAttributeToFilter('entity_id', null);
        } else {
//            var_dump($supplyNeedsTopFilter);die('sdasd');
            $sort = $supplyNeedsTopFilter['sort'];
            $dir = $supplyNeedsTopFilter['dir'];
//            $this->collection = $this->_supplyNeedsManagement->getProductSupplyNeedsCollection($topFilter, $sort, $dir);
            $this->collection = $collectionFactory->create();

            // get image
            $storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Store\Model\StoreManagerInterface');
            $path = $storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            );
            $path .= 'catalog/product';
            $edition = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\App\ProductMetadataInterface')
                ->getEdition();
            $rowId = strtolower($edition) == 'enterprise' ? 'row_id' : 'entity_id';
            /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute */
            $eavAttribute = \Magento\Framework\App\ObjectManager::getInstance()
                ->create('Magento\Eav\Model\ResourceModel\Entity\Attribute');
            $productImagesAttributeId = $eavAttribute->getIdByCode(\Magento\Catalog\Model\Product::ENTITY, 'image');
            $this->collection->getSelect()->joinLeft(
                array('catalog_product_entity_varchar_img' => $this->collection->getTable('catalog_product_entity_varchar')),
                "e.entity_id = catalog_product_entity_varchar_img.$rowId && 
                catalog_product_entity_varchar_img.attribute_id = $productImagesAttributeId && 
                catalog_product_entity_varchar_img.store_id = 0",
                array('')
            )->columns(array(
                'image' => 'catalog_product_entity_varchar_img.value',
                'image_url' => 'CONCAT("'.$path.'", catalog_product_entity_varchar_img.value)'
            ));


        }
//        $this->collection = $collectionFactory->create();
////        var_dump($this->collection->getSize().'xxd');
    }

    /**
     * @return array
     */
    public function getData()
    {
        $items = $this->getCollection()->toArray();
        return [
            'totalRecords' => count($items),
            'items' => array_values($items),
        ];
        
    }
}
