<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\Permission\Staff;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magestore\InventorySuccess\Api\Permission\PermissionManagementInterface;
use Magestore\InventorySuccess\Model\WarehouseFactory;

class AdminUserSaveAfter implements ObserverInterface
{
    /**
     * @var PermissionManagementInterface
     */
    protected $permissionManagement;

    /**
     * @var WarehouseFactory
     */
    protected $warehouseFactory;

    /**
     * @var string 
     */
    protected $controllerName;
    
    /**
     * @var \Magestore\InventorySuccess\Api\Logger\LoggerInterface
     */
    protected $logger;    
        

    /**
     * AdminUserSaveAfter constructor.
     * @param PermissionManagementInterface $permissionManagement
     * @param WarehouseFactory $warehouseFactory
     */
    public function __construct(
        PermissionManagementInterface $permissionManagement,
        RequestInterface $requestInterface,
        WarehouseFactory $warehouseFactory,
        \Magestore\InventorySuccess\Api\Logger\LoggerInterface $logger
    )
    {
        $this->permissionManagement = $permissionManagement;
        $this->warehouseFactory = $warehouseFactory;
        $this->controllerName = $requestInterface->getControllerName();
        $this->logger = $logger;
    }

    /**
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $adminUser = $observer->getDataObject();
        $staffId = $adminUser->getUserId();
        $links = $adminUser->getWarehouseLinks();
        
        if ($staffId && $this->controllerName == 'user') {
            try {
                $this->permissionManagement->setPermissionsByObject($this->warehouseFactory->create(), $staffId, $links['associated']);
            } catch (\Exception $e) {
                /* log issue */
               $this->logger->log($e->getMessage(), 'AdminUserSaveAfter');                
            }
        }
        return $this;
    }
}