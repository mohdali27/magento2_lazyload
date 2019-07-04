<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Order\Shipment\View;


class Warehouse extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;
    
    /**
     * @var \Magestore\InventorySuccess\Model\OrderProcess\DataProvider\ShipmentView
     */
    protected $shipmentFormDataProvider;
   
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Warehouse constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magestore\InventorySuccess\Model\OrderProcess\DataProvider\ShipmentView $shipmentViewDataProvider
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magestore\InventorySuccess\Model\OrderProcess\DataProvider\ShipmentView $shipmentViewDataProvider,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->shipmentViewDataProvider = $shipmentViewDataProvider;
        $this->urlBuilder = $context->getUrlBuilder();
        $this->moduleManager = $moduleManager;
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
     * Get ship Warehouse
     * 
     * @return \Magestore\InventorySuccess\Model\Warehouse
     */
    public function getShipWarehouse()
    {
        if(!$this->hasData('ship_warehouse')) {
            $warehouse = $this->shipmentViewDataProvider->getShipWarehouse($this->getShipment()->getId());
            $this->setData('ship_warehouse', $warehouse);
        }
        return $this->getData('ship_warehouse');
    }
    
    /**
     * 
     * @param \Magestore\InventorySuccess\Model\Warehouse $warehouse
     * @return string
     */
    public function getWarehouseDisplay($warehouse)
    {
        $html = '';
        if($warehouse->getId()) {
            $html .= $warehouse->getWarehouseName();
            $html .= ' (<a href="'.  $this->urlBuilder->getUrl('inventorysuccess/warehouse/edit', ['id' => $warehouse->getId()]) .'" target="_blank">';
            $html .= $warehouse->getWarehouseCode();
            $html .= '</a>)';
        }
        return $html;
    }

    /**
     * check shipment by dropship or not
     * @return bool|\Magestore\DropshipSuccess\Api\Data\DropshipSupplierShipmentInterface
     */
    public function isDropship()
    {
        if ($this->moduleManager->isEnabled('Magestore_DropshipSuccess')) {
            $shipmentId = $this->getShipment()->getId();
            /** @var \Magestore\DropshipSuccess\Service\DropshipSupplierShipmentService $dropshipSupplierShipmentService */
            $dropshipSupplierShipmentService = \Magento\Framework\App\ObjectManager::getInstance()->create(
                'Magestore\DropshipSuccess\Service\DropshipSupplierShipmentService'
            );
            /** @var \Magestore\DropshipSuccess\Api\Data\DropshipSupplierShipmentInterface $supplierShipment */
            $supplierShipment = $dropshipSupplierShipmentService->getSupplierShipmentByShipment($shipmentId);
            if ($supplierShipment->getId()) {
                return $supplierShipment;
            }
            return false;
        }
        return false;
    }

    /**
     * show supplier in shipment
     * @param \Magestore\DropshipSuccess\Api\Data\DropshipSupplierShipmentInterface $supplierShipment
     * @return string
     */
    public function getSupplierDisplay($supplierShipment)
    {
        $html = '';
        if ($supplierShipment->getId()) {
            $html .= ' <a href="'.  $this->urlBuilder->getUrl('suppliersuccess/supplier/edit', ['id' => $supplierShipment->getSupplierId()]) .'" target="_blank">';
            $html .= $supplierShipment->getSupplierName(). ' ('.$supplierShipment->getSupplierCode().')';
            $html .= '</a>';
        }
        return $html;
    }
}