<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Order\View;


class Warehouse extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface
     */
    protected $orderProcessService;

    /**
     * @var \Magestore\InventorySuccess\Model\WarehouseFactory
     */
    protected $warehouseFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\Warehouse\Options
     */
    protected $optionWarehouses;

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
        \Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface $orderProcessService,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        \Magestore\InventorySuccess\Model\Warehouse\Options $optionWarehouses,
        array $data = []
    )
    {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->coreRegistry = $registry;
        $this->orderProcessService = $orderProcessService;
        $this->warehouseFactory = $warehouseFactory;
        $this->optionWarehouses = $optionWarehouses;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->coreRegistry->registry('current_order');
    }

    /**
     * Get current warehouse
     *
     * @return \Magestore\InventorySuccess\Model\Warehouse
     */
    public function getWarehouse()
    {
        $warehouse = $this->coreRegistry->registry('current_warehouse');
        if (!$warehouse) {
            $warehouseId = $this->getOrder()->getWarehouseId();
            $warehouse = $this->warehouseFactory->create();
            $warehouse->getResource()->load($warehouse, $warehouseId);
            $this->coreRegistry->register('current_warehouse', $warehouse);
        }
        return $warehouse;
    }

    /**
     *
     * @param \Magestore\InventorySuccess\Model\Warehouse $warehouse
     * @return string
     */
    public function getWarehouseDisplay($warehouse)
    {
        $html = '';
        if ($warehouse->getId()) {
            $html .= '<a href="' . $this->urlBuilder->getUrl('inventorysuccess/warehouse/edit', ['id' => $warehouse->getId()]) . '" target="_blank">';
            $html .= $warehouse->getWarehouseName() . ' (' . $warehouse->getWarehouseCode();
            $html .= ')</a>';
        }
        return $html;
    }

    /**
     * Check current user can change warehouse for order
     * 
     * @return bool|mixed
     */
    public function canChangeWarehouse(){
        return $this->getOrder()->canShip() && $this->orderProcessService->canChangeOrderWarehouse();
    }

    /**
     * Get warehouse list to change
     *
     * @return array
     */
    public function getWarehouseOptions()
    {
        $this->optionWarehouses->showDummyRow();
        return $this->optionWarehouses->toOptionArray();
    }

    /**
     * Get Change Warehouse Url
     *
     * @return string
     */
    public function getChangeWarehouseUrl()
    {
        return $this->urlBuilder->getUrl(
            'inventorysuccess/order/changeWarehouse',
            [
                'order_id' => $this->getOrder()->getId(),
                'warehouse_id' => 'selected_warehouse_id'
            ]
        );
    }
}