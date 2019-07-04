<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Sales\Order\Grid;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;


class CollectionLoadBefore implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Grid\Collection
     */
    protected $orderGridCollection;

    /**
     * CollectionLoadBefore constructor.
     * @param \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $orderGridCollection
     * @param \Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface $orderProcessService
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $orderGridCollection
    )
    {
        $this->orderGridCollection = $orderGridCollection;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        /** @var  \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $collection */
        $collection = $observer->getEvent()->getCollection();
        if ($collection->getMainTable() != $this->orderGridCollection->getMainTable())
            return $this;

        $orderProcessService = \Magento\Framework\App\ObjectManager::getInstance()
            ->create('Magestore\InventorySuccess\Api\OrderProcess\OrderProcessServiceInterface');
        if ($orderProcessService->canChangeOrderWarehouse())
            return $this;
        $warehouseCollection = $orderProcessService->getViewWarehouseList();
        $warehouseIds = $warehouseCollection->getAllIds();
        if($warehouseIds) {
            $collection->addFieldToFilter('warehouse_id', ['in'=>$warehouseIds]);
        }
        return $this;
    }
}