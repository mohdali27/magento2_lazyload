<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Sales;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class OrderSaveBefore implements ObserverInterface
{

    /**
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    private $orderWarehouses = [];

    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magestore\InventorySuccess\Api\OrderProcess\PlaceNewOrderInterface
     */
    protected $placeNewOrder;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magestore\InventorySuccess\Api\OrderProcess\PlaceNewOrderInterface $placeNewOrder
    )
    {
        $this->objectManager = $objectManager;
        $this->coreRegistry = $coreRegistry;
        $this->placeNewOrder = $placeNewOrder;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (!$order->getWarehouseId()) {
            $warehouse = $this->placeNewOrder->getOrderWarehouse($order);
            $order->setData('warehouse_id', $warehouse->getWarehouseId());
        }
    }

}
