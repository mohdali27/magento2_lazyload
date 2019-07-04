<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Sales\Order\View;
//
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
//
//
class BeforeToHtml implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magestore\InventorySuccess\Model\WarehouseFactory
     */
    protected $warehouseFactory;

    /**
     * @var \Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface
     */
    protected $orderProcessService;

    /**
     * BeforeToHtml constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory
     * @param \Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface $orderProcessService
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magestore\InventorySuccess\Model\WarehouseFactory $warehouseFactory,
        \Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface $orderProcessService
    )
    {
        $this->registry = $registry;
        $this->warehouseFactory = $warehouseFactory;
        $this->orderProcessService = $orderProcessService;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Framework\View\Element\Template $block */
        $block = $observer->getEvent()->getBlock();
        if ($block->getNameInLayout() == 'sales_order_edit_process-order_ship-button'){
            $warehouse = $this->getCurrentWarehouse();
            if (!$this->orderProcessService->canCreateShipment($warehouse)) {
                $block->setTemplate(null);
            }
        }
        if ($block->getNameInLayout() == 'sales_order_edit_process-order_creditmemo-button'){
            $warehouse = $this->getCurrentWarehouse();
            if (!$this->orderProcessService->canCreateShipment($warehouse)) {
                $block->setTemplate(null);
            }
        }
    }

    /**
     * @return \Magestore\InventorySuccess\Model\Warehouse
     */
    protected function getCurrentWarehouse()
    {
        if (!$this->registry->registry('current_warehouse')) {
            $warehouseId = $this->getOrder()->getWarehouseId();
            $warehouse = $this->warehouseFactory->create();
            $warehouse->getResource()->load($warehouse, $warehouseId);
            $this->registry->register('current_warehouse', $warehouse);
        }
        return $this->registry->registry('current_warehouse');
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    protected function getOrder(){
        return $this->registry->registry('current_order');
    }
}