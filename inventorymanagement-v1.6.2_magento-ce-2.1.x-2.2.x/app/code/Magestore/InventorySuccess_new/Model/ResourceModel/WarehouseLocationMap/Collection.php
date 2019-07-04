<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\WarehouseLocationMap;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\DB\Select;
use Magento\Framework\ObjectManagerInterface;
use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product as WarehouseProductResource;

/**
 * Class Collection
 * @package Magestore\InventorySuccess\Model\ResourceModel\WarehouseLocationMap
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * @var \Magestore\Webpos\Model\Location\Location
     */
    protected $_location;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var mixed
     */
    protected $_moduleManager;

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\InventorySuccess\Model\WarehouseLocationMap', 'Magestore\InventorySuccess\Model\ResourceModel\WarehouseLocationMap');
    }

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        ObjectManagerInterface $objectManager,
        \Magento\Framework\Module\Manager $moduleManager
    )
    {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager);
        $this->_objectManager = $objectManager;
        $this->_moduleManager = $moduleManager;
    }

    /**
     * Init select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->setOrder('location_id','ASC');
        return $this;
    }

    /**
     * @return array
     */
    public function getAllWarehouseIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Select::ORDER);
        $idsSelect->reset(Select::LIMIT_COUNT);
        $idsSelect->reset(Select::LIMIT_OFFSET);
        $idsSelect->reset(Select::COLUMNS);
        $idsSelect->columns('main_table.warehouse_id');
        return $this->getConnection()->fetchCol($idsSelect);
    }

    /**
     * @return array
     */
    public function getAllLocationIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Select::ORDER);
        $idsSelect->reset(Select::LIMIT_COUNT);
        $idsSelect->reset(Select::LIMIT_OFFSET);
        $idsSelect->reset(Select::COLUMNS);
        $idsSelect->columns('main_table.location_id');
        return $this->getConnection()->fetchCol($idsSelect);
    }

    /**
     * @return $this
     */
    public function joinLocationCollection()
    {
        if (!$this->_moduleManager->isOutputEnabled('Magestore_Webpos')) {
            return $this;
        }
        $this->getSelect()->joinLeft(array('webpos_staff_location' => $this->getTable('webpos_staff_location'))
            , 'main_table.location_id = webpos_staff_location.location_id', array('display_name'))
            ->columns(['id'=>'main_table.location_id']);
        return $this;
    }

    /**
     * @return bool|\Magestore\Webpos\Model\Location\Location
     */
    public function getLocationCollection(){
        if($this->_location){
            return $this->_location;
        }
        if ($this->_moduleManager->isOutputEnabled('Magestore_Webpos')) {
            try {
                if (!$this->_location) {
                    $this->_location = $this->_objectManager->get('Magestore\Webpos\Model\Location\Location')->getCollection();
                }
            } catch (\Exception $ex) {
                return false;
            }
        } else {
            return false;
        }
        $this->_location->getSelect()->joinLeft(array('warehouse_location_map' => $this->_mainTable)
            , 'main_table.location_id = warehouse_location_map.location_id', array('warehouse_id'))
                ->columns(['position'=>'main_table.location_id']);
        return $this->_location;
    }

    /**
     * @param $collection
     * @param $warehouseId
     * @return mixed
     */
    public function setStockItemIntoCollectionByLocation($collection,$warehouseId)
    {
        $collection->getSelect()->joinLeft(array('warehouse_product' => $this->getTable(WarehouseProductResource::MAIN_TABLE))
            , 'e.entity_id = warehouse_product.product_id '
                . 'AND warehouse_product.'. WarehouseProductInterface::WAREHOUSE_ID .' = '.$warehouseId, array('total_qty','qty'))
            ->columns(['qty_to_ship'=>new \Zend_Db_Expr('warehouse_product.total_qty - warehouse_product.qty')]);
        return $collection;
    }
}