<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Sales;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class OrderItemSaveBefore implements ObserverInterface
{

    /**
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $coreRegistry
    )
    {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order\Item $orderItem */
        $orderItem = $observer->getEvent()->getItem();
        $this->convertConfigurableItemQty($orderItem);
        $beforeItem = $this->_objectManager->create('Magento\Sales\Model\Order\Item');
        if ($orderItem->getId()) {
            $beforeItem->load($orderItem->getId());
        }
        if (!$this->_coreRegistry->registry('os_beforeOrderItem' . $orderItem->getId())) {
            $this->_coreRegistry->register('os_beforeOrderItem' . $orderItem->getId(), $beforeItem);
        }
    }

    /**
     * Convert order item qty from configurable product to children items
     *
     * @param \Magento\Sales\Model\Order\Item $orderItem
     */
    public function convertConfigurableItemQty($orderItem)
    {
        $parentItem = $orderItem->getParentItem();
        if ($parentItem && $parentItem->getProductType() == Configurable::TYPE_CODE) {
            $orderItem->setQtyCanceled($parentItem->getQtyCanceled());
            $orderItem->setQtyInvoiced($parentItem->getQtyInvoiced());
            $orderItem->setQtyShipped($parentItem->getQtyShipped());
            $orderItem->setQtyRefunded($parentItem->getQtyRefunded());
            $orderItem->setQtyReturned($parentItem->getQtyReturned());
        }
    }

}
