<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Webpos\Location;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magestore\InventorySuccess\Model\Options\Location\Warehouse;
use Magestore\InventorySuccess\Model\WarehouseLocationMapFactory;

/**
 * Class WebposLocationEditForm
 * @package Magestore\InventorySuccess\Observer\Webpos\Location
 */
class WebposLocationEditForm implements ObserverInterface
{
    /**
     * @var Warehouse
     */
    protected $_warehouseOptions;

    /**
     * @var WarehouseLocationMapFactory
     */
    protected $_warehouseLocationMap;

    /**
     * WebposLocationEditForm constructor.
     * @param Warehouse $warehouseOptions
     * @param WarehouseLocationMapFactory $warehouseLocationMap
     */
    public function __construct(
        Warehouse $warehouseOptions,
        WarehouseLocationMapFactory $warehouseLocationMap
    ){
        $this->_warehouseOptions = $warehouseOptions;
        $this->_warehouseLocationMap = $warehouseLocationMap;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $fieldSet = $observer->getFieldSet();
        $modelData = $observer->getModelData();
        $warehouseLocationMap = $this->_warehouseLocationMap->create()->load($modelData->getLocationId(), 'location_id');
        if($warehouseLocationMap->getWarehouseId()){
            $modelData->setWarehouseId($warehouseLocationMap->getWarehouseId());
        }
        $fieldSet->addField('warehouse_id', 'select', array(
            'label'     => __('Location'),
            'name'      => 'warehouse_id',
            'values'   => $this->_warehouseOptions->toOptionArray($modelData->getLocationId())
        ));
        return $this;
    }
}