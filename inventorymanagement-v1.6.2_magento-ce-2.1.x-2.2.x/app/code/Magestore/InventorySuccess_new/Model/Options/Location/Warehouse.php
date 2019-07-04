<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Options\Location;

use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection as WareHouseCollection;
use Magestore\InventorySuccess\Model\ResourceModel\WarehouseLocationMap\Collection as MapCollection;

/**
 * Class Warehouse
 * @package Magestore\InventorySuccess\Model\Options\Location
 */
class Warehouse implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var WareHouseCollection
     */
    protected $_wareHouseCollection;

    /**
     * @var MapCollection
     */
    protected $_mapCollection;

    /**
     * Warehouse constructor.
     * @param WareHouseCollection $wareHouseCollection
     * @param MapCollection $mapCollection
     */
    public function __construct(
        WareHouseCollection $wareHouseCollection,
        MapCollection $mapCollection
    )
    {
        $this->_wareHouseCollection = $wareHouseCollection;
        $this->_mapCollection = $mapCollection;
    }

    /**
     * Return array of options as value-label pairs.
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray($locationId = null)
    {
        if($locationId){
            $this->_mapCollection->addFieldToFilter('location_id',['neq'=>$locationId]);
        }
        $warehouseMapIDs = $this->_mapCollection->getAllWarehouseIds();
        if ($warehouseMapIDs) {
            $this->_wareHouseCollection->addFieldToFilter('warehouse_id', ['nin' => $warehouseMapIDs]);
        }
        $options = [
            ['value' => 0, 'label' => __("Don't link to any Locations")],
            ['value' => -1, 'label' => __('Create a new Location')]
        ];
        if(is_array($this->_wareHouseCollection->toOptionArray())) {
            $options = array_merge($options, $this->_wareHouseCollection->toOptionArray());
        }
        return $options;
    }

    /**
     * Return array of options as value-label pairs.
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function getAllOptionArray()
    {
        $options = [
            ['value' => -1, 'label' => __('Create a new Location')]
        ];
        if(is_array($this->_wareHouseCollection->toOptionArray())) {
            $options = array_merge($options, $this->_wareHouseCollection->toOptionArray());
        }
        return $options;
    }
}
