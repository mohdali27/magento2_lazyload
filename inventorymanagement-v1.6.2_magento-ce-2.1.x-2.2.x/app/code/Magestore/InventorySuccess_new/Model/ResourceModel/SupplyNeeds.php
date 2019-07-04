<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel;

use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;

class SupplyNeeds extends AbstractResource
{

    const TEMP_WAREHOUSE_PRODUCTS = 'temp_warehouse_product';
    const TEMP_SHIPMENT_ITEM = 'temp_shipment_item';
    const TEMP_WAREHOUSE_SHIPMENT_ITEM = 'temp_warehouse_shipment_item';

    /**
     * @var SupplyNeeds\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_coreResource;

    /**
     * @var Warehouse\Product\CollectionFactory
     */
    protected $_warehouseProductCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory
     */
    protected $_itemShipmentcollectionFactory;

    /**
     * @var Warehouse\Shipment\Item\CollectionFactory
     */
    protected $_itemWarehouseShipmentcollectionFactory;
    /*
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {

    }

    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\SupplyNeeds\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ResourceConnection $coreResource,
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product\CollectionFactory $warehouseProductCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory $itemShipmentcollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory $shipmentcollectionFactory,
        \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Shipment\Item\CollectionFactory $itemWarehouseShipmentcollectionFactory
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_objectManager = $objectManager;
        $this->_coreResource = $coreResource;
        $this->_warehouseProductCollectionFactory = $warehouseProductCollectionFactory;
        $this->_itemShipmentcollectionFactory = $itemShipmentcollectionFactory;
        $this->_shipmentcollectionFactory = $shipmentcollectionFactory;
        $this->_itemWarehouseShipmentcollectionFactory = $itemWarehouseShipmentcollectionFactory;
    }

    /**
     * @param $topFilter
     * @return SupplyNeeds\Product\Collection
     */
    public function getProductSupplyNeedsCollection($topFilter, $sort, $dir)
    {
        $topFilter = unserialize(base64_decode($topFilter));
        $salesPeriod = $topFilter['sales_period'];
        $forecastDateTo = $topFilter['forecast_date_to'];
        if ($salesPeriod == \Magestore\InventorySuccess\Model\SupplyNeeds::CUSTOM_RANGE) {
            $fromDate = date('Y-m-d', strtotime($topFilter['from_date']));
            $toDate = date('Y-m-d', strtotime($topFilter['to_date']));
        } else {
            $fromDate = $this->_objectManager->get(
                '\Magestore\InventorySuccess\Model\SupplyNeeds\SupplyNeedsManagement'
            )->getFromDateBySalePeriod($salesPeriod);
            $toDate = date('Y-m-d', strtotime('-1 day', strtotime(date('Y-m-d'))));
        }
        $fromDate .= ' 00:00:00';
        $toDate .= ' 23:59:59';
        $numberOfDaySalesPeriod = ceil((strtotime($toDate) - strtotime($fromDate)) / (60 * 60 * 24));
        $numberForecastDay = floor((strtotime($forecastDateTo) - strtotime(date('Y-m-d'))) / (60 * 60 * 24));
        $topFilter['from_date'] = $fromDate;
        $topFilter['to_date'] = $toDate;
        $topFilter['number_of_day_sales_period'] = $numberOfDaySalesPeriod;
        $topFilter['number_forecast_day'] = $numberForecastDay;
        $this->_removeTermTable($this->_getTempTables());
        // create temp_warehouse_product table
        $this->_createTermWarehouseProduct($topFilter);
        // create temp_shipment_item table
        $this->_createTermWarehouseShipmentItems($topFilter);
        /** @var \Magestore\InventorySuccess\Model\ResourceModel\SupplyNeeds\Product\Collection $collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->getSelect()->join(
            ['warehouse_product' => $this->_coreResource->getTableName(self::TEMP_WAREHOUSE_PRODUCTS)],
            'e.entity_id = warehouse_product.product_id',
            [
                'current_qty' => 'warehouse_product.current_qty'
            ]
        );
        $collection->getSelect()->join(
            ['warehouse_shipment_item' => $this->_coreResource->getTableName(self::TEMP_WAREHOUSE_SHIPMENT_ITEM)],
            "e.entity_id = warehouse_shipment_item.product_id",
            [
                'total_sold' => 'warehouse_shipment_item.total_sold'
            ]
        )
            ->group('warehouse_shipment_item.product_id')
            //->where("GREATEST((warehouse_shipment_item.total_sold / {$numberOfDaySalesPeriod} * {$numberForecastDay} - warehouse_product.current_qty),0) > ?", 0)
        ;
        $collection->getSelect()->columns([
            'avg_qty_ordered' => new \Zend_Db_Expr("(warehouse_shipment_item.total_sold / {$numberOfDaySalesPeriod})"),
            'total_sold' => "warehouse_shipment_item.total_sold",
            'current_qty' => 'warehouse_product.current_qty',
            'availability_date' => new \Zend_Db_Expr("DATE_ADD(CURDATE(),INTERVAL(FLOOR(warehouse_product.current_qty / (warehouse_shipment_item.total_sold / {$numberOfDaySalesPeriod}))) DAY)"),
            'supply_needs' => new \Zend_Db_Expr("CEIL(GREATEST((CEIL(warehouse_shipment_item.total_sold / {$numberOfDaySalesPeriod} * {$numberForecastDay}) - warehouse_product.current_qty),0))"),
        ]);

        $collection->setIsGroupCountSql(true);
        $collection->getSelectCountSql();
        $collection = $this->getSortData($collection, $sort, $dir, $topFilter);
        return $collection;

    }

    protected function _getTempTables()
    {
        return [
            self::TEMP_WAREHOUSE_PRODUCTS,
            self::TEMP_SHIPMENT_ITEM,
            self::TEMP_WAREHOUSE_SHIPMENT_ITEM
        ];
    }

    /**
     * Create TEMP_WAREHOUSE_PRODUCTS
     * @param $topFilter
     */
    protected function _createTermWarehouseProduct($topFilter)
    {
        $warehouseIds = $topFilter['warehouse_ids'];
        $collection = $this->_warehouseProductCollectionFactory->create()
            ->addFieldToFilter(WarehouseProductInterface::WAREHOUSE_ID, ['in' => $warehouseIds]);
        $collection->addFieldToSelect('product_id');
        $collection->getSelect()->columns([
                'current_qty' => new \Zend_Db_Expr("SUM(total_qty)")
            ]
        );
        $collection->getSelect()->group('product_id');
        $this->_createTempTable(self::TEMP_WAREHOUSE_PRODUCTS, $collection);
    }

    /**
     * Create TEMP_WAREHOUSE_SHIPMENT_ITEM
     * @param $topFilter
     */
    protected function _createTermWarehouseShipmentItems($topFilter)
    {
        $fromDate = $topFilter['from_date'];
        $toDate = $topFilter['to_date'];
        $warehouseIds = $topFilter['warehouse_ids'];

//        $collection = $this->_itemWarehouseShipmentcollectionFactory->create()
//            ->addFieldToFilter('created_at', ['gteq' => $fromDate])
//            ->addFieldToFilter('created_at', ['lteq' => $toDate])
//            ->addFieldToFilter('warehouse_id', ['in' => $warehouseIds]);
//        $collection->addFieldToSelect('product_id');
//        $collection->getSelect()->columns([
//                'total_sold' => new \Zend_Db_Expr("SUM(qty_shipped)")
//            ]
//        );

        $collection = $this->_itemShipmentcollectionFactory->create()
            ->addFieldToFilter('created_at', ['gteq' => $fromDate])
            ->addFieldToFilter('created_at', ['lteq' => $toDate])
            ->addFieldToFilter('warehouse_id', ['in' => $warehouseIds]);
        $collection->getSelect()->join(
            ['warehouse_shipment' => $this->_coreResource->getTableName('sales_shipment')],
            "main_table.parent_id = warehouse_shipment.entity_id",
            [
                'total_sold' => new \Zend_Db_Expr("SUM(main_table.qty)")
            ]
        );
        $collection->getSelect()->group('product_id');
        $this->_createTempTable(self::TEMP_WAREHOUSE_SHIPMENT_ITEM, $collection);
    }

    /**
     * Create TEMP_SHIPMENT_ITEM
     * @param $topFilter
     */
    protected function _createTermShipmentItems($topFilter)
    {
        $fromDate = $topFilter['from_date'];
        $toDate = $topFilter['to_date'];
        $shipmentCollection = $this->_shipmentcollectionFactory->create();
        $shipmentCollection->addFieldToSelect('entity_id')
            ->addFieldToSelect('created_at')
            ->addFieldToFilter('created_at', ['gteq' => $fromDate])
            ->addFieldToFilter('created_at', ['lteq' => $toDate]);
        $shipmentIds = [];
        foreach ($shipmentCollection as $shipment) {
            $shipmentIds[] = $shipment->getId();
        }
        $collection = $this->_itemShipmentcollectionFactory->create()
            ->addFieldToFilter('parent_id', ['in' => $shipmentIds]);
        $collection->addFieldToSelect('product_id');
        $collection->getSelect()->columns([
                'total_sold' => new \Zend_Db_Expr("SUM(qty)"),
                'name' => 'name'
            ]
        );
        $collection->getSelect()->group('product_id');
        $this->_createTempTable(self::TEMP_SHIPMENT_ITEM, $collection);
    }

    protected function _removeTermTable($tempTables) {
        foreach ($tempTables as $tempTable) {
            $sql = "DROP TABLE IF EXISTS " . $this->_coreResource->getTableName($tempTable) . ";";
            $this->_coreResource->getConnection('core_write')->query($sql);
        }
    }

    protected function _createTempTable($tempTable, $collection) {
        $sql = "CREATE TEMPORARY TABLE " . $this->_coreResource->getTableName($tempTable) . " ";
        $sql .= $collection->getSelect()->__toString();
        $this->_coreResource->getConnection('core_write')->query($sql);
    }

    /**
     * @param $collection
     * @param $column
     * @param $topFilter
     * @return mixed
     */
    public function filterDateCallback($collection, $column, $topFilter)
    {
        $filter = $column->getFilter()->getValue();
        $field = $this->_getRealFieldFromAlias($column->getIndex(), $topFilter);
        if (isset($filter['from']) && $filter['from'] != '') {
            $from = $filter['from'];
            $collection->getSelect()->where($field . ' >= \'' . $from->format('Y-m-d') . '\'');
        }
        if (isset($filter['to']) && $filter['to'] != '') {
            $to = $filter['to'];
            $collection->getSelect()->where($field . ' <= \'' . $to->format('Y-m-d') . '\'');
        }

        $collection->setIsGroupCountSql(true);
        $collection->getSelectCountSql();
        $collection->setResetHaving(true);
        return $collection;
    }

    /**
     * @param $collection
     * @param $column
     * @param $topFilter
     * @return mixed
     */
    public function filterNumberCallback($collection, $column, $topFilter)
    {
        $filter = $column->getFilter()->getValue();
        $field = $this->_getRealFieldFromAlias($column->getIndex(), $topFilter);
        if (isset($filter['from']) && $filter['from'] != '') {
            $collection->getSelect()->where($field . ' >= ' . $filter['from']);
        }
        if (isset($filter['to']) && $filter['to'] != '') {
            $collection->getSelect()->where($field . ' <= ' . $filter['to']);
        }

        //echo $collection->getSelect()->__toString();
        //die;

        $collection->setIsGroupCountSql(true);
        $collection->getSelectCountSql();
        return $collection;
    }

    /**
     * @param $alias
     * @param $topFilter
     * @return string|\Zend_Db_Expr
     */
    protected function _getRealFieldFromAlias($alias, $topFilter) {

        $topFilter = unserialize(base64_decode($topFilter));
        $salesPeriod = $topFilter['sales_period'];
        $forecastDateTo = $topFilter['forecast_date_to'];
        if ($salesPeriod == \Magestore\InventorySuccess\Model\SupplyNeeds::CUSTOM_RANGE) {
            $fromDate = date('Y-m-d', strtotime($topFilter['from_date']));
            $toDate = date('Y-m-d', strtotime($topFilter['to_date']));
        } else {
            $fromDate = $this->_objectManager->get(
                '\Magestore\InventorySuccess\Model\SupplyNeeds\SupplyNeedsManagement'
            )->getFromDateBySalePeriod($salesPeriod);
            $toDate = date('Y-m-d');
        }
        $fromDate .= ' 00:00:00';
        $toDate .= ' 23:59:00';
        $numberOfDaySalesPeriod = floor((strtotime($toDate) - strtotime($fromDate))/(60*60*24));
        $numberForecastDay = floor((strtotime($forecastDateTo) - strtotime($toDate))/(60*60*24));

        switch ($alias) {
            case 'avg_qty_ordered':
                $field = new \Zend_Db_Expr("ROUND((warehouse_shipment_item.total_sold / {$numberOfDaySalesPeriod}),2)");
                break;
            case 'total_sold':
                $field = "warehouse_shipment_item.total_sold";
                break;
            case 'current_qty':
                $field = "warehouse_product.current_qty";
                break;
            case 'availability_date':
                $field = new \Zend_Db_Expr("DATE_ADD(CURDATE(),INTERVAL(warehouse_product.current_qty / (warehouse_shipment_item.total_sold / {$numberOfDaySalesPeriod})) DAY)");
                break;
            case 'supply_needs':
                $field = new \Zend_Db_Expr("CEIL(GREATEST((CEIL(warehouse_shipment_item.total_sold / {$numberOfDaySalesPeriod} * {$numberForecastDay}) - warehouse_product.current_qty),0))");
                break;
        }
        return $field;
    }

    public function getSortData($collection, $sort, $dir, $topFilter)
    {
        $salesPeriod = $topFilter['sales_period'];
        $forecastDateTo = $topFilter['forecast_date_to'];
        if ($salesPeriod == \Magestore\InventorySuccess\Model\SupplyNeeds::CUSTOM_RANGE) {
            $fromDate = date('Y-m-d', strtotime($topFilter['from_date']));
            $toDate = date('Y-m-d', strtotime($topFilter['to_date']));
        } else {
            $fromDate = $this->_objectManager->get(
                '\Magestore\InventorySuccess\Model\SupplyNeeds\SupplyNeedsManagement'
            )->getFromDateBySalePeriod($salesPeriod);
            $toDate = date('Y-m-d');
        }
        $fromDate .= ' 00:00:00';
        $toDate .= ' 23:59:00';
        $numberOfDaySalesPeriod = floor((strtotime($toDate) - strtotime($fromDate))/(60*60*24));
        $numberForecastDay = floor((strtotime($forecastDateTo) - strtotime($toDate))/(60*60*24));

        switch ($sort) {
            case 'avg_qty_ordered':
                $collection->getSelect()->order(new \Zend_Db_Expr("(warehouse_shipment_item.total_sold / {$numberOfDaySalesPeriod}) " . $dir));
                break;
            case 'total_sold':
                $collection->getSelect()->order("warehouse_shipment_item.total_sold ". $dir);
                break;
            case 'current_qty':
                $collection->getSelect()->order("warehouse_product.current_qty ". $dir);
                break;
            case 'availability_date':
                $collection->getSelect()->order(new \Zend_Db_Expr("DATE_ADD(CURDATE(),INTERVAL(warehouse_product.current_qty / (warehouse_shipment_item.total_sold / {$numberOfDaySalesPeriod})) DAY) ". $dir));
                break;
            case 'supply_needs':
                $collection->getSelect()->order( new \Zend_Db_Expr("(GREATEST((total_sold / {$numberOfDaySalesPeriod} * {$numberForecastDay} - current_qty),0)) ". $dir));
                break;

                break;
        }

        return $collection;
    }

    public function getMoreInformationToExport($topFilter)
    {
        $topFilter = unserialize(base64_decode($topFilter));
        $salesPeriod = $topFilter['sales_period'];
        $warehouseIds = $topFilter['warehouse_ids'];
        $forecastDateTo = $topFilter['forecast_date_to'];
        if ($salesPeriod == \Magestore\InventorySuccess\Model\SupplyNeeds::CUSTOM_RANGE) {
            $fromDate = date('Y-m-d', strtotime($topFilter['from_date']));
            $toDate = date('Y-m-d', strtotime($topFilter['to_date']));
        } else {
            $fromDate = $this->_objectManager->get(
                '\Magestore\InventorySuccess\Model\SupplyNeeds\SupplyNeedsManagement'
            )->getFromDateBySalePeriod($salesPeriod);
            $toDate = date('Y-m-d');
        }

        $infor = [];
        $infor[] = [__('SUPPLY NEEDS')];
        $warehouseCollection = $this->_objectManager->get(
            '\Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory'
        )->create()->addFieldToFilter('warehouse_id', ['in' => $warehouseIds]);
        $warehouseName = '';
        foreach ($warehouseCollection as $warehouse) {
            $warehouseName .= $warehouse->getWarehouseName()."\n";
        }
        $infor[] = [__('Location(s)'), $warehouseName];
        $infor[] = [__('Sales Period'), __('From date'), $fromDate, __('To date'), $toDate];
        $infor[] = [__('Forecast Supply Needs To'), $forecastDateTo];
        $infor[] = [];
        return $infor;
    }
}