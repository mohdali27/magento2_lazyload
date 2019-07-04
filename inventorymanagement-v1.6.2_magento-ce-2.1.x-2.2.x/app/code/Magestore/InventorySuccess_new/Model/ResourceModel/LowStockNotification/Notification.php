<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification;

use Magestore\InventorySuccess\Api\Data\Warehouse\ProductInterface as WarehouseProductInterface;
use Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Product as WarehouseProductResource;

/**
 * Resource Model Supplier
 */
class Notification extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_coreResource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     * @var int
     */
    protected $batchCount;

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('os_lowstock_notification', 'notification_id');
    }

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        $batchCount = 1000,
        $connectionName = null
    )
    {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_coreResource = $context->getResources();
        $this->_connection = $this->_coreResource->getConnection();
        $this->batchCount = $batchCount;
        parent::__construct($context, $connectionName);
    }

    /**
     * @param $rule
     * @param $productIds
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductNotificationBySystem($rule, $productIds)
    {
        $lowstockThreshold = $rule['lowstock_threshold'];
        $lowstockThresholdType = $rule['lowstock_threshold_type'];
        $lowstockThresholdQty = $rule['lowstock_threshold_qty'];
        $salesPeriod = $rule['sales_period'];
        $toDate = date('Y-m-d');
        $fromDate = $this->getFromDateBySalePeriod($salesPeriod);
        $fromDate .= ' 00:00:00';
        $toDate .= ' 23:59:59';
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('name');
        $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
        $collection->addFieldToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $collection->getSelect()->join(
            ['warehouse_product' => $this->_coreResource->getTableName(WarehouseProductResource::MAIN_TABLE)],
            'e.entity_id = warehouse_product.product_id AND warehouse_product.' . WarehouseProductInterface::WAREHOUSE_ID . ' = ' . WarehouseProductInterface::DEFAULT_SCOPE_ID,
            [
                'current_qty' => 'warehouse_product.total_qty'
            ]
        );
        if ($lowstockThresholdType == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_LOWSTOCK_THRESHOLD_PRODUCT_QTY) {
            $collection->getSelect()->where("warehouse_product.total_qty <= ?", $lowstockThresholdQty);
        }
        if ($lowstockThresholdType == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_LOWSTOCK_THRESHOLD_SALE_DAY) {
            $collection->getSelect()->join(
                ['order_item' => $this->_coreResource->getTableName('sales_order_item')],
                'e.entity_id = order_item.product_id',
                [
                    'total_sold' => new \Zend_Db_Expr("SUM(order_item.qty_ordered)"),
                    'sold_per_day' => new \Zend_Db_Expr("SUM(order_item.qty_ordered) / {$salesPeriod}"),
                    'availability_date' => new \Zend_Db_Expr("DATE_ADD(CURDATE(),INTERVAL(FLOOR(warehouse_product.total_qty / (SUM(order_item.qty_ordered) / {$salesPeriod}))) DAY)"),
                    'availability_days' => new \Zend_Db_Expr("FLOOR(GREATEST(warehouse_product.total_qty / (SUM(order_item.qty_ordered) / {$salesPeriod}),0))")
                ]
            )
                ->where("order_item.created_at >= ?", $fromDate)
                ->where("order_item.created_at <= ?", $toDate)
                ->group('order_item.product_id')
                ->having("GREATEST((SUM(order_item.qty_ordered) / {$salesPeriod} * {$lowstockThreshold} - warehouse_product.total_qty),0) > ?", 0);
        }

        if ($collection->count() > 0) {
            $type = \Magestore\InventorySuccess\Model\LowStockNotification\Notification::NOTIFY_TYPE_SYSTEM;
            if ($lowStockNotificationId = $this->addProductToNotification($rule, $collection, $type, null)) {
                try {
                    /** add notification to inbox */
                    $this->addToInbox($rule, $lowStockNotificationId, null);
                    /** @var  \Magestore\InventorySuccess\Model\LowStockNotification\RuleFactory $ruleModel */
                    $ruleModel = \Magento\Framework\App\ObjectManager::getInstance()->create(
                        'Magestore\InventorySuccess\Model\LowStockNotification\RuleFactory'
                    )->create();
                    $ruleModel->load($rule['rule_id']);
                    $ruleModel->setData('updated_at', date('Y-m-d H:i:s'));
                    $ruleModel->save();
                } catch (\Exception $e) {
                    return null;
                }
                return $lowStockNotificationId;
            }
        }
    }

    /**
     * @param $rule
     * @param $productIds
     * @param $warehouseIds
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getProductNotificationByWarehouse($rule, $productIds, $warehouseIds)
    {
        $lowstockThreshold = $rule['lowstock_threshold'];
        $lowstockThresholdType = $rule['lowstock_threshold_type'];
        $lowstockThresholdQty = $rule['lowstock_threshold_qty'];
        $salesPeriod = $rule['sales_period'];
        $toDate = date('Y-m-d');
        $fromDate = $this->getFromDateBySalePeriod($salesPeriod);
        $fromDate .= ' 00:00:00';
        $toDate .= ' 23:59:59';
        $warehouseIds = explode(',', $warehouseIds);
        $result = [];
        foreach ($warehouseIds as $warehouseId) {
            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
            $collection = $this->_productCollectionFactory->create();
            $collection->addAttributeToSelect('name');
            $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
            $collection->addFieldToFilter('type_id', \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
            $collection->getSelect()->join(
                ['warehouse_product' => $this->_coreResource->getTableName(WarehouseProductResource::MAIN_TABLE)],
                "e.entity_id = warehouse_product.product_id and warehouse_product." . WarehouseProductInterface::WAREHOUSE_ID . " = {$warehouseId}",
                [
                    'current_qty' => 'warehouse_product.total_qty'
                ]
            );
            if ($lowstockThresholdType == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_LOWSTOCK_THRESHOLD_PRODUCT_QTY) {
                $collection->getSelect()->where("warehouse_product.total_qty <= ?", $lowstockThresholdQty);
            }
            if ($lowstockThresholdType == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_LOWSTOCK_THRESHOLD_SALE_DAY) {
                $collection->getSelect()->join(
                    ['warehouse_shipment_item' => $this->_coreResource->getTableName('sales_shipment_item')],
                    "e.entity_id = warehouse_shipment_item.product_id and warehouse_shipment_item.warehouse_id = {$warehouseId}",
                    [
                        'total_sold' => new \Zend_Db_Expr("SUM(warehouse_shipment_item.qty)"),
                        'sold_per_day' => new \Zend_Db_Expr("SUM(warehouse_shipment_item.qty) / {$salesPeriod}"),
                        'availability_date' => new \Zend_Db_Expr("DATE_ADD(CURDATE(),INTERVAL(FLOOR(warehouse_product.total_qty / (SUM(warehouse_shipment_item.qty) / {$salesPeriod}))) DAY)"),
                        'availability_days' => new \Zend_Db_Expr("FLOOR(GREATEST(warehouse_product.total_qty / (SUM(warehouse_shipment_item.qty) / {$salesPeriod}),0))")
                    ]
                );
                $collection->getSelect()->joinLeft(
                     ['warehouse_shipment' => $this->_coreResource->getTableName('sales_shipment')],
                     "warehouse_shipment_item.parent_id = warehouse_shipment.entity_id",
                     [
                         'total_sold_xxx' => new \Zend_Db_Expr("SUM(warehouse_shipment_item.qty)")
                     ]
                 )

                    ->where("warehouse_shipment.created_at >= ?", $fromDate)
                    ->where("warehouse_shipment.created_at <= ?", $toDate)
                    ->group('warehouse_shipment_item.product_id')
                    ->having("GREATEST((SUM(warehouse_shipment_item.qty) / {$salesPeriod} * {$lowstockThreshold} - warehouse_product.total_qty),0) > ?", 0);
            }
            if ($collection->count() > 0) {
                $type = \Magestore\InventorySuccess\Model\LowStockNotification\Notification::NOTIFY_TYPE_WAREHOUSE;
                if ($lowStockNotificationId = $this->addProductToNotification($rule, $collection, $type, $warehouseId)) {
                    try {
                        /** add notification to inbox */
                        $this->addToInbox($rule, $lowStockNotificationId, $warehouseId);
                        /** @var  \Magestore\InventorySuccess\Model\LowStockNotification\RuleFactory $ruleModel */
                        $ruleModel = \Magento\Framework\App\ObjectManager::getInstance()->create(
                            'Magestore\InventorySuccess\Model\LowStockNotification\RuleFactory'
                        )->create();
                        $ruleModel->load($rule['rule_id']);
                        $ruleModel->setData('updated_at', date('Y-m-d H:i:s'));
                        $ruleModel->save();
                    } catch (\Exception $e) {
                        return null;
                    }
                    $result[$warehouseId] = $lowStockNotificationId;
                }
            }
        }
        return $result;
    }

    /**
     * @param $salesPeriod
     * @return bool|string
     */
    public function getFromDateBySalePeriod($salesPeriod)
    {
        $fromDate = date('Y-m-d', strtotime('-' . $salesPeriod . ' days', strtotime(date('Y-m-d'))));
        return $fromDate;
    }

    /**
     * @param $rule
     * @param $collection
     * @param $type
     * @param $warehouseId
     * @return null
     */
    public function addProductToNotification($rule, $collection, $type, $warehouseId)
    {
        $lowStockNotificationId = $this->createNewLowStockNotification($rule, $type, $warehouseId);
        if ($lowStockNotificationId) {
            try {
                $row = [];
                /** insert product to low stock notification */
                foreach ($collection as $col) {
                    /** type low stock notification by product qty */
                    if ($rule['lowstock_threshold_type'] == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_LOWSTOCK_THRESHOLD_PRODUCT_QTY) {
                        $rows[] = [
                            'notification_id' => $lowStockNotificationId,
                            'product_id' => $col->getEntityId(),
                            'product_sku' => $col->getSku(),
                            'product_name' => $col->getName(),
                            'current_qty' => $col->getCurrentQty()
                        ];
                    }
                    /** type low stock notification by sale day */
                    if ($rule['lowstock_threshold_type'] == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_LOWSTOCK_THRESHOLD_SALE_DAY) {
                        $rows[] = [
                            'notification_id' => $lowStockNotificationId,
                            'product_id' => $col->getEntityId(),
                            'product_sku' => $col->getSku(),
                            'product_name' => $col->getName(),
                            'current_qty' => $col->getCurrentQty(),
                            'sold_per_day' => $col->getSoldPerDay(),
                            'total_sold' => $col->getTotalSold(),
                            'availability_days' => $col->getAvailabilityDays(),
                            'availability_date' => $col->getAvailabilityDate()
                        ];
                    }
                    if (count($rows) == $this->batchCount) {
                        $this->_connection->insertMultiple($this->_coreResource->getTableName('os_lowstock_notification_product'), $rows);
                        $rows = [];
                    }
                }
                if (!empty($rows)) {
                    $this->_connection->insertMultiple($this->_coreResource->getTableName('os_lowstock_notification_product'), $rows);
                }
                return $lowStockNotificationId;
            } catch (\Exception $e) {
                return null;
            }
        }
    }

    /**
     * @param $rule
     * @param $type
     * @param $warehouseId
     * @return int
     */
    public function createNewLowStockNotification($rule, $type, $warehouseId)
    {
        /** @var  \Magestore\InventorySuccess\Model\LowStockNotification\NotificationFactory $lowStockNotification */
        $lowStockNotification = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\LowStockNotification\NotificationFactory'
        )->create();
        $warehouseName = '';
        if ($warehouseId) {
            /** @var  \Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Collection $warehouseCollection */
            $warehouseCollection = \Magento\Framework\App\ObjectManager::getInstance()->create(
                '\Magestore\InventorySuccess\Model\ResourceModel\Warehouse\CollectionFactory'
            )->create();
            /** @var \Magestore\InventorySuccess\Model\Warehouse $warehouse */
            $warehouse = $warehouseCollection->addFieldToFilter('warehouse_id', $warehouseId)
                ->getFirstWarehouse();
            if ($warehouse->getId()) {
                $warehouseName = $warehouse->getWarehouseName();
            }
        }
        $lowStockNotification->setData('rule_id', $rule['rule_id'])
            ->setData('update_type', $type)
            ->setData('notifier_emails', $rule['notifier_emails'])
            ->setData('lowstock_threshold_type', $rule['lowstock_threshold_type'])
            ->setData('lowstock_threshold_qty', $rule['lowstock_threshold_qty'])
            ->setData('sales_period', $rule['sales_period'])
            ->setData('warehouse_id', $warehouseId)
            ->setData('warehouse_name', $warehouseName)
            ->setData('warning_message', $rule['warning_message']);
        try {
            $lowStockNotification->save();
            return $lowStockNotification->getId();
        } catch (\Exception $e) {
            return null;
        }

    }

    /**
     * add notificaiton to inbox
     * @param $rule
     * @param $lowStockNotificationId
     * @param $warehouseId
     */
    public function addToInbox($rule, $lowStockNotificationId, $warehouseId)
    {
        /** @var  \Magento\AdminNotification\Model\Inbox $adminInbox */
        $adminInbox = \Magento\Framework\App\ObjectManager::getInstance()->create(
            'Magento\AdminNotification\Model\InboxFactory'
        )->create();
        /** @var  \Magento\Backend\Model\UrlInterface $backendUrl */
        $backendUrl = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Backend\Model\UrlInterface'
        );
        $severity = \Magento\Framework\Notification\MessageInterface::SEVERITY_NOTICE;
        $title = __('OS Low Stock Notifications for System');
        $url = $backendUrl->getUrl('inventorysuccess/lowstocknotification_notification/notify', ['id' => $lowStockNotificationId]);
        $description = $rule['description'] . ". " . $rule['warning_message']; // . '. You can check at '. $url;
        if ($warehouseId) {
            /** @var  \Magestore\InventorySuccess\Model\Warehouse $warehouse */
            $warehouse = \Magento\Framework\App\ObjectManager::getInstance()->create(
                '\Magestore\InventorySuccess\Model\WarehouseFactory'
            )->create();
            $warehouse->load($warehouseId);
            $warehouseName = $warehouse->getWarehouseName();
            $title = __("OS Low Stock Notifications for Location: %1", $warehouseName);
        }
        $adminInbox->setData('severity', $severity)
            ->setData('title', $title)
            ->setData('description', $description)
            ->setData('url', $url);
        try {
            $adminInbox->save();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param $notificationId
     * @return array
     */
    public function getMoreInformationToExport($notificationId)
    {
        $notificationModel = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\LowStockNotification\NotificationFactory'
        )->create();
        $notification = $notificationModel->load($notificationId);
        $info = [];
        $info[] = [__('LOW STOCK NOTIFICATION')];
        $info[] = [__('Warning Message'), $notification->getWarningMessage()];
        $info[] = [__('Created at'), $notification->getCreatedAt()];
        if ($notification->getWarehouseName()) {
            $info[] = [__('Update type'), __('Location')];
            $info[] = [__('Location'), $notification->getWarehouseName()];
        } else {
            $info[] = [__('Update type'), __('System')];
        }
        $info[] = [__('Notifier emails'), $notification->getNotifierEmails()];
        $info[] = [__('Low Stock Threshold (days)'), $notification->getLowstockThreshold()];
        $info[] = [__('Sales Period (days)'), $notification->getSalesPeriod()];
        $info[] = [];
        return $info;
    }
}