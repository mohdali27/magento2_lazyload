<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Sales;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;


class CreditmemoItemSaveAfter implements ObserverInterface
{
    /**
     * @var \Magestore\InventorySuccess\Api\Logger\LoggerInterface
     */
    protected $logger;    

    /**
     * @var \Magestore\InventorySuccess\Api\OrderProcess\CreateCreditmemoInterface 
     */
    protected $createCreditmemo;
    

    public function __construct(  
        \Magestore\InventorySuccess\Api\Logger\LoggerInterface $logger,
        \Magestore\InventorySuccess\Api\OrderProcess\CreateCreditmemoInterface $createCreditmemo 
    ) {
        $this->logger = $logger;
        $this->createCreditmemo = $createCreditmemo;
    }    
    
    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $item = $observer->getEvent()->getCreditmemoItem();
        
        try{
            $this->createCreditmemo->execute($item);
            
        }catch(\Exception $e) {
            /* log issue */
            $this->logger->log($e->getMessage(), 'CreditmemoItemSaveAfter');
        }
    }    
}