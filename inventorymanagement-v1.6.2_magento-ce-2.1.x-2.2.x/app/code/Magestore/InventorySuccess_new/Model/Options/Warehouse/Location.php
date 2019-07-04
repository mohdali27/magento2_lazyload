<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Options\Warehouse;

use Magestore\InventorySuccess\Model\ResourceModel\WarehouseLocationMap\Collection as MapCollection;
use Magestore\InventorySuccess\Model\WarehouseLocationMap;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class Warehouse
 * @package Magestore\InventorySuccess\Model\Options\Location
 */
class Location implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var MapCollection
     */
    protected $_mapCollection;

    /**
     * @var WarehouseLocationMap
     */
    protected $_warehouseLocationMap;

    /**
     * @var mixed
     */
    protected $_locationCollection;

    /**
     * @var mixed
     */
    protected $_moduleManager;

    /**
     * Location constructor.
     * @param ObjectManagerInterface $objectManager
     * @param MapCollection $mapCollection
     * @param WarehouseLocationMap $warehouseLocationMap
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        MapCollection $mapCollection,
        WarehouseLocationMap $warehouseLocationMap
    )
    {
        $this->_objectManager = $objectManager;
        $this->_mapCollection = $mapCollection;
        $this->_warehouseLocationMap = $warehouseLocationMap;
        $this->_moduleManager = $this->_objectManager->create('Magento\Framework\Module\Manager');

    }

    /**
     * Return array of options as value-label pairs.
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        if ($this->_moduleManager->isOutputEnabled('Magestore_Webpos')) {
            try {
                $options = [];
                $this->_locationCollection = $this->_objectManager->create('Magestore\Webpos\Model\ResourceModel\Location\Location\Collection');
                $options[] = ['value' => 0, 'label' => __("Don't associate to Location")];
                $options[] = ['value' => -1, 'label' => __('Create a new Location')];
                $locationMapIDs = $this->_mapCollection->getAllLocationIds();
                if ($locationMapIDs) {
                    $this->_locationCollection->addFieldToFilter('location_id', ['nin' => $locationMapIDs]);
                }
                if(is_array($this->_locationCollection->toOptionArray())) {
                    $options = array_merge($options, $this->_locationCollection->toOptionArray());
                }
                return $options;
            } catch (\Exception $ex) {
                return false;
            }
        }
        return false;
    }

    /**
     * Return array of options as value-label pairs.
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function getOptionArray()
    {
        if ($this->_moduleManager->isOutputEnabled('Magestore_Webpos')) {
            try {
                $this->_locationCollection = $this->_objectManager->create('Magestore\Webpos\Model\ResourceModel\Location\Location\Collection');
                $options = $this->_locationCollection->toOptionArray();
                return $options;
            } catch (\Exception $ex) {
                return false;
            }
        }
        return false;
    }
}
