<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\TransferStock\Send\Prepare;

use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\CollectionFactory;

/**
 * Class ProductDataProvider
 */
class ProductDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
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
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
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
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
//        /** @var \Magestore\InventorySuccess\Model\Locator\Locator $locator */
        $locator = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\Locator\LocatorFactory'
        )->create();
        $transferStockId = $locator->getSesionByKey('transfer_send_stock_prepare');
//        /** @var \Magestore\InventorySuccess\Model\TransferStock $transferStock */
        $transferStock = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\TransferStockFactory'
        )->create();
        $transferStock->load($transferStockId);
        if ($transferStock->getId()) {
            $warehouseSourceId = $transferStock->getSourceWarehouseId();
        }
        $this->collection = $collectionFactory->create()
            ->addFieldToFilter('warehouse_id', $warehouseSourceId);
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }

//    /**
//     * Get data
//     *
//     * @return array
//     */
//    public function getData()
//    {
//        if (!$this->getCollection()->isLoaded()) {
//            $this->getCollection()->load();
//        }
//        $items = $this->getCollection()->toArray();
//        return [
//            'totalRecords' => $this->getCollection()->getSize(),
//            'items' => array_values($items),
//        ];
//    }
//
//    /**
//     * Add field to select
//     *
//     * @param string|array $field
//     * @param string|null $alias
//     * @return void
//     */
//    public function addField($field, $alias = null)
//    {
////        var_dump($this->addFieldStrategies[$field]);die();
////        var_dump($field);
//        if (isset($this->addFieldStrategies[$field])) {
//            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
//        } else {
//            parent::addField($field, $alias);
//        }
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function addFilter(\Magento\Framework\Api\Filter $filters)
//    {
//        if (isset($this->addFilterStrategies[$filters->getField()])) {
//            $this->addFilterStrategies[$filters->getField()]
//                ->addFilter(
//                    $this->getCollection(),
//                    $filters->getField(),
//                    [$filters->getConditionType() => $filters->getValue()]
//                );
//        } else {
//            parent::addFilter($filters);
//        }
//    }
}
