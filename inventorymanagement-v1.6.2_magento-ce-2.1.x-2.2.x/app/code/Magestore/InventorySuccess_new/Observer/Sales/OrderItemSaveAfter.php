<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Sales;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;


class OrderItemSaveAfter implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    
    /**
     * @var \Magestore\InventorySuccess\Api\Logger\LoggerInterface
     */
    protected $logger;    

    /**
     * @var \Magestore\InventorySuccess\Api\OrderProcess\PlaceNewOrderInterface 
     */
    protected $placeNewOrder;
    

    public function __construct(  
        \Magento\Framework\Registry $coreRegistry,
        \Magestore\InventorySuccess\Api\Logger\LoggerInterface $logger,
        \Magestore\InventorySuccess\Api\OrderProcess\PlaceNewOrderInterface $placeNewOrder 
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->logger = $logger;
        $this->placeNewOrder = $placeNewOrder;
    }    
    
    /**
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer)
    {
        $item = $observer->getEvent()->getItem();

        $itemBefore = $this->_coreRegistry->registry('os_beforeOrderItem'. $item->getId());
        $itemBefore = $itemBefore ? $itemBefore : $this->_coreRegistry->registry('os_beforeOrderItem');
        
        try{
            $this->placeNewOrder->execute($item, $itemBefore);
            
        }catch(\Exception $e) {
            /* log issue */
            $this->logger->log($e->getMessage(), 'orderItemSaveAfter');
        }
    }    
}