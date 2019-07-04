<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Webpos\Location;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magestore\InventorySuccess\Model\Options\Warehouse\Location;
use Magestore\InventorySuccess\Model\WarehouseLocationMapFactory;
use Magento\Ui\Component\Form;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;

/**
 * Class InventorysuccessWarehouseEditForm
 * @package Magestore\InventorySuccess\Observer\Webpos\Location
 */
class InventorysuccessWarehouseEditForm implements ObserverInterface
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var
     */
    protected $_location;
    
    /**
     * @var Location
     */
    protected $_locationOptions;

    /**
     * @var WarehouseLocationMapFactory
     */
    protected $_warehouseLocationMap;
    
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var mixed
     */
    protected $_moduleManager;

    /**
     * InventorysuccessWarehouseEditForm constructor.
     * @param Location $locationOptions
     * @param WarehouseLocationMapFactory $warehouseLocationMap
     */
    public function __construct(
        Location $locationOptions,
        ObjectManagerInterface $objectManager,
        UrlInterface $urlBuilder,
        WarehouseLocationMapFactory $warehouseLocationMap
    ){
        $this->_locationOptions = $locationOptions;
        $this->_warehouseLocationMap = $warehouseLocationMap;
        $this->_objectManager = $objectManager;
        $this->urlBuilder = $urlBuilder;
        $this->_moduleManager = $this->_objectManager->create('Magento\Framework\Module\Manager');
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        return $this;
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
        $fieldSet = $observer->getFieldSet();
        $modelData = $observer->getModelData();
        $warehouseLocationMap = $this->_warehouseLocationMap->create()->load($modelData->getWarehouseId(), 'warehouse_id');
        $locationId = $warehouseLocationMap->getLocationId();
        $data = $fieldSet->getData();
        if($locationId){
            $url = $this->urlBuilder->getUrl('webposadmin/location/edit',['id'=>$locationId]);
            $data['location_id'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => 'input',
                            'componentType' => Form\Field::NAME,
                            'label' => __('POS Location'),
                            'dataType' => 'text',
                            'value' => $this->_location->load($locationId)->getDisplayName(),
                            'dataScope' => 'location_id',
                            'additionalInfo' => __('You can link POS Location to another Location <a href="%1">here</a>',$url),
                            'disabled' => true,
                            'sortOrder' => 130,
                        ],
                    ],
                ],
            ];
        }else {
            $data['location_id'] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement' => 'select',
                            'componentType' => Form\Field::NAME,
                            'label' => __('POS Location'),
                            'options' => $this->_locationOptions->toOptionArray(),
                            'dataType' => 'text',
                            'dataScope' => 'location_id',
                            'sortOrder' => 130,
                        ],
                    ],
                ],
            ];
        }
        $fieldSet->setData($data);
        return $this;
    }
}