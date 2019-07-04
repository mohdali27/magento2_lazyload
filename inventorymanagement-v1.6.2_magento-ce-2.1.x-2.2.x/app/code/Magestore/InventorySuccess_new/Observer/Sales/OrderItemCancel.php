<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Sales;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;


class OrderItemCancel implements ObserverInterface
{
    /**
     * @var \Magestore\InventorySuccess\Api\Logger\LoggerInterface
     */
    protected $logger;    
    
    /**
     * @var \Magestore\InventorySuccess\Api\OrderProcess\CancelOrderInterface 
     */
    protected $cancelOrder;


    public function __construct(  
        \Magestore\InventorySuccess\Api\Logger\LoggerInterface $logger,
        \Magestore\InventorySuccess\Api\OrderProcess\CancelOrderInterface $cancelOrder
            
    ) {
        $this->logger = $logger;
        $this->cancelOrder = $cancelOrder;
    }    
    
    /**
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Order\Item $item */
        $item = $observer->getEvent()->getItem();

        try{

            $this->cancelOrder->execute($item);
            
        }catch(\Exception $e) {
           $this->logger->log($e->getMessage(), 'OrderItemCancel');
        }
    }    
}