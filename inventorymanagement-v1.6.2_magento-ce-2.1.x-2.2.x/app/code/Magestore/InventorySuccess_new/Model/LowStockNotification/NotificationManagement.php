<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Created by PhpStorm.
 * User: zero
 * Date: 06/04/2016
 * Time: 09:29
 */

namespace Magestore\InventorySuccess\Model\LowStockNotification;

class NotificationManagement implements \Magestore\InventorySuccess\Api\LowStockNotification\NotificationManagementInterface
{
    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\NotificationFactory
     */
    protected $_notificationResourceFactory;

    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\NotificationFactory $notificationResourceFactory
    ) {
        $this->_notificationResourceFactory = $notificationResourceFactory;
    }

    /**
     * @param $rule
     * @param $productIds
     * @return mixed
     */
    public function getProductNotificationBySystem($rule, $productIds)
    {
        return $this->_notificationResourceFactory->create()->getProductNotificationBySystem($rule, $productIds);
    }

    public function getProductNotificationByWarehouse($rule, $productIds, $warehouseIds)
    {
        return $this->_notificationResourceFactory->create()->getProductNotificationByWarehouse($rule, $productIds, $warehouseIds);
    }
}