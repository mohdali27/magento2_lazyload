<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\LowStockNotification\Notification;


class Email extends \Magestore\InventorySuccess\Block\LowStockNotification\AbstractNotification
{
    /**
     * @param $id
     * @return string
     */
    public function getDownloadUrl($id)
    {
        /** @var \Magento\Framework\Url $urlBuilder */
        $urlBuilder = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Framework\Url'
        );
        
        return $urlBuilder->getUrl('inventorysuccess/lowstocknotification/download', ['notification_id' =>$id]);
    }

    public function getWarehouseInformation($warehouseIds) {
        $warehouses = [];
        /** @var  \Magestore\InventorySuccess\Model\Warehouse $warehouseModel */
        $warehouseModel = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\WarehouseFactory'
        )->create();
        foreach ($warehouseIds as $key => $id) {
            $warehouseId = $key;
            $warehouseModel->load($warehouseId);
            $warehouseInfo = [];
            if ($warehouseModel->getId()) {
                $warehouseInfo['warehouse_id'] = $warehouseId;
                $warehouseInfo['warehouse_name'] = $warehouseModel->getWarehouseName();
                $warehouseInfo['notification_id'] = $id;
                $warehouses[] = $warehouseInfo;
            }
        }
        return $warehouses;
    }
}