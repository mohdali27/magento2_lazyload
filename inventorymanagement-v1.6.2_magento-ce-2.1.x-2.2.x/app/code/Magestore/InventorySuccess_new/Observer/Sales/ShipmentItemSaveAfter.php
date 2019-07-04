<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Sales;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;


class ShipmentItemSaveAfter implements ObserverInterface
{
    
    /**
     * @var \Magestore\InventorySuccess\Api\Logger\LoggerInterface
     */
    protected $logger;    
    
    /**
     * @var \Magestore\InventorySuccess\Api\OrderProcess\CreateShipmentInterface 
     */
    protected $createShipment;


    public function __construct(  
        \Magestore\InventorySuccess\Api\Logger\LoggerInterface $logger,
        \Magestore\InventorySuccess\Api\OrderProcess\CreateShipmentInterface $createShipment
            
    ) {
        $this->logger = $logger;
        $this->createShipment = $createShipment;
    }    
    
    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $shipmentItem = $observer->getEvent()->getShipmentItem();
        
        try{
        
            $this->createShipment->execute($shipmentItem);
 
        }catch(\Exception $e) {
            /* log issue */
           $this->logger->log($e->getMessage(), 'ShipmentItemSaveAfter');
        }
    }    
}