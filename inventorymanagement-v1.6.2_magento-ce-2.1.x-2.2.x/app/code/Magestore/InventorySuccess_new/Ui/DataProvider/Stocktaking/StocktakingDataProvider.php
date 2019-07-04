<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Ui\DataProvider\Stocktaking;

use Magestore\InventorySuccess\Model\ResourceModel\Stocktaking\CollectionFactory;

/**
 * Class ProductDataProvider
 */
class StocktakingDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Payment collection
     *
     * @var \Magestore\InventorySuccess\Model\ResourceModel\Stocktaking\Collection
     */
    protected $collection;


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
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $select = $this->getCollection()->getSelect()
            ->columns(['status', 'warehouse_name',  'warehouse_id',  'created_by', 'created_at', 'stocktaking_code'])
            ->join(
                [
                    'warehouse' => $this->getCollection()->getTable("os_warehouse")
                ],
                "main_table.warehouse_id = warehouse.warehouse_id",
                [
                    'warehouse.telephone',
                    'warehouse.country_id'
                ]
            );
        $items = [];
        $result = $this->getCollection()->getConnection()->query($select);
        while ($row = $result->fetch()) {
            $items[] = $row;
        }
        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => array_values($items),
        ];
    }

}
