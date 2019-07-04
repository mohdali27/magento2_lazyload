<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Warehouse\Location;

use Magestore\InventorySuccess\Api\Warehouse\Location\MappingManagementInterface;
use Magestore\InventorySuccess\Api\Warehouse\WarehouseManagementInterface;
use Magestore\InventorySuccess\Api\Db\QueryProcessorInterface;
use Magestore\InventorySuccess\Model\WarehouseLocationMapFactory;
use Magestore\InventorySuccess\Model\WarehouseFactory;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class MappingManagement
 * @package Magestore\InventorySuccess\Model\Warehouse\Location
 */
class MappingManagement implements MappingManagementInterface
{

    /**
     * MappingManagement contruct
     */
    protected function _construct()
    {
        /* do nothing */
    }

    /**
     * Mapping between location id and warehouse id with data sample like [$locationId => $warehouseId]
     *
     * @var array
     */
    protected $warehouseMapping = [];

    /**
     * @var WarehouseLocationMapFactory
     */
    protected $_warehouseLocationMap;

    /**
     * @var WarehouseFactory
     */
    protected $_warehouseFactory;

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
     * @var mixed
     */
    protected $_warehouseManagement;

    /**
     * @var QueryProcessorInterface
     */
    protected $_queryProcessor;

    /**
     * MappingManagement constructor.
     * @param WarehouseLocationMapFactory $warehouseLocationMap
     * @param WarehouseFactory $warehouseFactory
     * @param ObjectManagerInterface $objectManager
     * @param WarehouseManagementInterface $warehouseManagement
     * @param QueryProcessorInterface $queryProcessor
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param null $connectionName
     */
    public function __construct(
        WarehouseLocationMapFactory $warehouseLocationMap,
        WarehouseFactory $warehouseFactory,
        ObjectManagerInterface $objectManager,
        WarehouseManagementInterface $warehouseManagement,
        \Magestore\InventorySuccess\Api\Db\QueryProcessorInterface $queryProcessor,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    )
    {
        $this->_warehouseLocationMap = $warehouseLocationMap;
        $this->_warehouseFactory = $warehouseFactory;
        $this->_objectManager = $objectManager;
        $this->_warehouseManagement = $warehouseManagement;
        $this->_queryProcessor = $queryProcessor;
        $this->_moduleManager = $this->_objectManager->create('Magento\Framework\Module\Manager');
    }

    /**
     * @param array $data
     * @return $this
     */
    public function createListMapping(array $data = [])
    {
        $locationIds = [];
        foreach ($data as $item) {
            if (isset($item['warehouse_id']) && isset($item['location_id'])) {
                $this->mappingWarehouseToLocation($item['warehouse_id'], $item['location_id'], true);
                $locationIds[] = $item['location_id'];
            }
        }
        $allLocationIds = $this->_warehouseLocationMap->create()->getCollection()->getAllLocationIds();
        $deleteMapping = array_diff_key($allLocationIds, $locationIds);
        if (count($deleteMapping)) {
            /* start queries processing */
            $this->_queryProcessor->start();
            /* prepare to remove mapping, then add queries to Processor */
            $this->_prepareRemoveMapping($deleteMapping);
            /* process queries in Processor */
            $this->_queryProcessor->process();
        }
        return $this;
    }

    /**
     * @param $warehouseId
     * @param $locationId
     * @param bool $force
     * @return bool
     */
    public function mappingWarehouseToLocation($warehouseId, $locationId, $force = false)
    {
        if ($warehouseId < 1 && $locationId < 1) {
            return false;
        }
        if ($this->_moduleManager->isOutputEnabled('Magestore_Webpos')) {
            try {
                if (!$this->_location) {
                    $this->_location = $this->_objectManager->get('Magestore\Webpos\Model\Location\Location');
                }
            } catch (\Exception $ex) {
                return false;
            }
        } else {
            return false;
        }
        $warehouse = $this->_warehouseFactory->create();

        if ($locationId == 0 || $warehouseId == 0) {
            $oldwarehouseLocationMap = $this->_warehouseLocationMap->create();
            if ($locationId) {
                $oldwarehouseLocationMap->load($locationId, 'location_id');
            } else {
                $oldwarehouseLocationMap->load($warehouseId, 'warehouse_id');
            }
            $oldwarehouseLocationMap->delete();
        } elseif ($locationId == -1) {
            try {
                $warehouse->load($warehouseId);
                $location = $this->_location;
                $location->setDisplayName($warehouse->getWarehouseName())
                    ->setDescription($warehouse->getWarehouseName())
                    ->setAddress($warehouse->getWarehouseName())
                    ->save();
                $locationId = $location->getId();
            } catch (\Exception $ex) {
                return false;
            }
        } elseif ($warehouseId == -1) {
            try {

                $location = $this->_location->load($locationId);
                $code = $location->getDisplayName() . ' ' . $location->getLocationId();
                $check_warehouse = $warehouse->getCollection()
                    ->addFieldToFilter(\Magestore\InventorySuccess\Model\Warehouse::WAREHOUSE_CODE, $location->getDisplayName() . ' ' . $location->getLocationId())->getFirstItem();
                $count_warehouse = $warehouse->getCollection()->getSize();
                if ($check_warehouse->getData()) {
                    $code = $location->getDisplayName() . ' ' . $location->getLocationId() . '(' . ($count_warehouse + 1) . ')';
                }
                $warehouse->setWarehouseName($location->getDisplayName())
                    ->setWarehouseCode($code)
                    ->setStatus(\Magestore\InventorySuccess\Model\Warehouse\Options\Status::STATUS_ENABLED)
                    ->save();
                $warehouseId = $warehouse->getId();
            } catch (\Exception $ex) {
                return false;
            }
        }
        if ($locationId > 0 && $warehouseId > 0) {
            $oldwarehouseLocationMap = $this->_warehouseLocationMap->create();
            $oldwarehouseLocationMap->load($warehouseId, 'warehouse_id');
            try {
                if ($oldwarehouseLocationMap->getLocationId() && $locationId != $oldwarehouseLocationMap->getLocationId() && $force) {
                    $oldwarehouseLocationMap->delete();
                } elseif ($oldwarehouseLocationMap->getLocationId()) {
                    return false;
                }
                $newWarehouseLocationMap = $this->_warehouseLocationMap->create();
                $newWarehouseLocationMap->load($locationId, 'location_id')
                    ->setLocationId($locationId)
                    ->setWarehouseId($warehouseId)
                    ->save();
            } catch (\Exception $ex) {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    /**
     * @param array $deleteMapping
     * @return $this
     */
    protected function _prepareRemoveMapping($deleteMapping = [])
    {
        $conditions = [];
        if (count($deleteMapping)) {
            $conditions['location_id IN (?)'] = $deleteMapping;
        }
        /* add query to Processor */
        $this->_queryProcessor->addQuery(['type' => QueryProcessorInterface::QUERY_TYPE_DELETE,
            'condition' => $conditions,
            'table' => $this->_warehouseLocationMap->create()->getResource()->getMainTable()
        ]);
        return $this;
    }

    /**
     * @param $locationId
     * @return array
     */
    public function getProductIdsByLocationId($locationId)
    {
        $productIds = [];
        $warehouseId = $this->getWarehouseIdByLocationId($locationId);
        $result = $this->_warehouseManagement->getListProduct($warehouseId);
        if ($result->getSize()) {
            foreach ($result as $item) {
                $productIds[$item->getProductId()] = $item->getProductId();
            }
        }
        return $productIds;
    }

    /**
     * @param $locationId
     * @return mixed
     */
    public function getWarehouseIdByLocationId($locationId)
    {
        if (!isset($this->warehouseMapping[$locationId])) {
            $warehouseId = $this->_warehouseLocationMap->create()->load($locationId, 'location_id')->getWarehouseId();
            $this->warehouseMapping[$locationId] = $warehouseId;
        }
        /*
        if (!$warehouseId) {
            $warehouseId = $this->_warehouseManagement->getPrimaryWarehouse()->getId();
        }
         */
        return $this->warehouseMapping[$locationId];
    }
}          