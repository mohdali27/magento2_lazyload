<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Order\Shipment\Create;


class SelectWarehouse extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    
    /**
     * @var \Magestore\InventorySuccess\Model\OrderProcess\DataProvider\ShipmentForm
     */
    protected $shipmentFormDataProvider;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magestore\InventorySuccess\Model\OrderProcess\DataProvider\ShipmentForm $shipmentFormDataProvider,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->shipmentFormDataProvider = $shipmentFormDataProvider;
        parent::__construct($context, $data);
    }   
    
    /**
     * Retrieve invoice order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getShipment()->getOrder();
    }

    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        return $this->coreRegistry->registry('current_shipment');
    }    
    
    /**
     * Get list of availabel warehouses to create shipment
     * 
     * @return array
     */
    public function getAvailableWarehouses()
    {
        if(!$this->hasData('available_warehouses')) {
            $warehouses = $this->shipmentFormDataProvider->getAvailableWarehouses($this->getOrder());
            $warehouses = $this->_formatWarehouseList($warehouses);
            $this->setData('available_warehouses', $warehouses);
        }
        return $this->getData('available_warehouses');
    }
    
    /**
     * 
     */
    public function getWarehouseJson()
    {
        return \Zend_Json::encode($this->getAvailableWarehouses());
    }
    
    /**
     * Format warehouse list before returning
     * 
     * @param array $warehouses
     * @return array
     */
    protected function _formatWarehouseList($warehouses)
    {
        $formatList = [];
        if(count($warehouses)) {
            foreach($warehouses as $warehouseId => $warehouse){
                $warehouseInfo = $warehouse['info'];
                $stockStatusInfo = '';
                if($warehouse['lack_qty']) {
                    $stockStatusInfo = '('.__('lack %1 items', $warehouse['lack_qty']).')';
                }
                $formatList[$warehouseId] = [
                    'label' => $warehouseInfo['warehouse_code'] .' - '. $warehouseInfo['warehouse_name'] .' '. $stockStatusInfo,
                    'items' => $warehouse['items'],
                    'lack_qty' => $warehouse['lack_qty'],
                    'info' => $warehouse['info'],
                ];
            }
        }
        return $formatList;
    }
    
    /**
     * 
     * @return string
     */
    public function getRequestStockUrl()
    {
        return $this->getUrl('inventorysuccess/transferstock_request/new', ['order_id' => $this->getOrder()->getIncrementId()]);
    }

}