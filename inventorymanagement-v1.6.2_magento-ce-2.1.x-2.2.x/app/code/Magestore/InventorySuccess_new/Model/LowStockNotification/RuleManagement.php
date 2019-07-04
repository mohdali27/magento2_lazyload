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

class RuleManagement implements \Magestore\InventorySuccess\Api\LowStockNotification\RuleManagementInterface
{

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_productCollection;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var array
     */
    protected $_productIds;

    public function __construct() {
        $this->_productCollection = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Catalog\Model\ResourceModel\Product\Collection'
        );
        $this->_productFactory = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Catalog\Model\ProductFactory'
        );
    }

    /**
     * @param \Magestore\InventorySuccess\Model\LowStockNotification\Rule $ruleModel
     * @return string
     */
    public function getNewNextTime($ruleModel)
    {
        /** @var \Magento\Framework\Stdlib\DateTime\DateTime $dateTime */
        $dateTime = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Framework\Stdlib\DateTime\DateTime'
        );
        $now = $dateTime->timestamp();

        /** if update time type is daily  */
        if ($ruleModel->getData('update_time_type') == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TIME_TYPE_DAILY) {
            $times = $ruleModel->getData('specific_time');
            $times = explode(',', $times);
            $nextDate = date('Y-m-d', $now);
            $timeNow = date('H', $now) + 1;
            $nextTime = '';
            foreach ($times as $time) {
                if ($timeNow <= $time) {
                    $nextTime = $time;
                    break;
                }
            }
            if (!$nextTime) {
                $nextTime = $times[0];
                $nextDate = date('Y-m-d', strtotime('+1 day', strtotime(date('Y-m-d', $now))));
            }
            $newNextTime = $nextDate. ' '. $nextTime. ':00:00';
            return $newNextTime;
        }
        /** if update time type is monthly */
        if ($ruleModel->getData('update_time_type') == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TIME_TYPE_MONTHLY) {
            $times = explode(',', $ruleModel->getData('specific_time'));
            $days = explode(',', $ruleModel->getData('specific_day'));
            $months = explode(',', $ruleModel->getData('specific_month'));
            $timeNow = date('H', $now);
            $dayNow = date('d', $now);
            $monthNow = date('m', $now);
            $yearNow = date('Y', $now);
            $nextYear = $yearNow;
            $nextMonth = '';
            $nextDay = '';
            $nextTime = '';
            /** compare month */
            foreach ($months as $month) {
                if ($monthNow <= $month) {
                    $nextMonth = $month;
                    break;
                }
            }
            if (!$nextMonth) {
                $nextYear = $yearNow + 1;
                $nextMonth = $months[0];
                $nextDay = $days[0];
                $nextTime = $times[0];
                $newNextTime = $nextYear . '-' . $nextMonth . '-'. $nextDay . ' ' . $nextTime . ':00:00';
                return $newNextTime;
            }
            if ($nextMonth > $monthNow) {
                $nextDay = $days[0];
                $nextTime = $times[0];
                $newNextTime = $nextYear . '-' . $nextMonth . '-'. $nextDay . ' ' . $nextTime . ':00:00';
                return $newNextTime;
            }
            /** compare day */
            foreach ($days as $day) {
                if ($dayNow <= $day) {
                    $nextDay = $day;
                    break;
                }
            }
            if (!$nextDay) {
                $nextYear = $yearNow;
                if ($nextMonth == $monthNow)
                    $nextMonth = $nextMonth + 1;
                if ($nextMonth > max($months)) {
                    $nextYear = $yearNow + 1;
                    $nextMonth = $months[0];
                } else {
                    foreach ($months as $month) {
                        if ($nextMonth <= $month) {
                            $nextMonth = $month;
                            break;
                        }
                    }
                }
                $nextDay = $days[0];
                $nextTime = $times[0];
                $newNextTime = $nextYear . '-' . $nextMonth . '-'. $nextDay . ' ' . $nextTime . ':00:00';
                return $newNextTime;
            }
            if ($nextDay > $dayNow) {
                $nextTime = $times[0];
                $newNextTime = $nextYear . '-' . $nextMonth . '-'. $nextDay . ' ' . $nextTime . ':00:00';
                return $newNextTime;
            }
            /** compare time */
            foreach ($times as $time) {
                if ($timeNow <= $time) {
                    $nextTime = $time;
                    break;
                }
            }
            if (!$nextTime) {
                $nextTime = $times[0];
                $nextDay = $nextDay + 1;
                if ($nextDay > max($days)) {
                    $nextDay = $days[0];
                    $nextMonth = $nextMonth + 1;
                    if ($nextMonth > max($months)) {
                        $nextYear = $yearNow + 1;
                        $nextMonth = $months[0];
                    } else {
                        foreach ($months as $month) {
                            if ($nextMonth <= $month) {
                                $nextMonth = $month;
                                break;
                            }
                        }
                        $nextYear = $yearNow;
                    }
                } else {
                    foreach ($days as $day) {
                        if ($nextDay <= $day) {
                            $nextDay = $day;
                            break;
                        }
                    }
                }
            }
            $newNextTime = $nextYear . '-' . $nextMonth . '-'. $nextDay . ' ' . $nextTime . ':00:00';
            return $newNextTime;
        }
    }

    /**
     * Get array of product ids which are matched by rule
     * @param \Magestore\InventorySuccess\Model\LowStockNotification\Rule $ruleModel
     * @return array
     */
    public function getListProductIdsInRule($ruleModel)
    {
        $this->_productIds = [];
        $ruleModel->setCollectedAttributes([]);
        $ruleModel->getConditions()->collectValidatedAttributes($this->_productCollection);

        \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Framework\Model\ResourceModel\Iterator'
        )->walk(
            $this->_productCollection->getSelect(),
            [[$this, 'callbackValidateProduct']],
            [
                'attributes' => $ruleModel->getCollectedAttributes(),
                'product' => $this->_productFactory->create(),
                'rule_model' => $ruleModel
            ]
        );
        return $this->_productIds;
    }

    /**
     * Callback function for product matching
     *
     * @param array $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $product = clone $args['product'];
        $product->setData($args['row']);
        $ruleModel = $args['rule_model'];
        $websites = $this->_getWebsitesMap();
        foreach ($websites as $websiteId => $defaultStoreId) {
            $product->setStoreId($defaultStoreId);
            if ($ruleModel->getConditions()->validate($product)) {
                $this->_productIds[] = $product->getId();
            }
        }
    }

    /**
     * Prepare website map
     *
     * @return array
     */
    protected function _getWebsitesMap()
    {
        $map = [];
        $websites = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Store\Model\StoreManagerInterface'
        )->getWebsites();
        foreach ($websites as $website) {
            // Continue if website has no store to be able to create catalog rule for website without store
            if ($website->getDefaultStore() === null) {
                continue;
            }
            $map[$website->getId()] = $website->getDefaultStore()->getId();
        }
        return $map;
    }

    /**
     * @return array
     */
    public function getAvailableRules()
    {
        /** @var \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Rule $ruleResourceModel */
        $ruleResourceModel = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Rule'
        );
        return $ruleResourceModel->getAvailableRules();
    }

    /**
     * @param $productSystem
     * @param $productWarehouse
     * @param $notifierEmails
     */
    public function sendEmailNotification($productSystem, $productWarehouse, $notifierEmails)
    {
        /** @var \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder */
        $transportBuilder = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Framework\Mail\Template\TransportBuilder'
        );
        /** @var  \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig */
        $scopeConfig = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Framework\App\Config\ScopeConfigInterface'
        );
        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = \Magento\Framework\App\ObjectManager::getInstance()->create(
            'Magento\Store\Model\StoreManagerInterface'
        );
        $storeId = $storeManager->getDefaultStoreView()->getStoreId();

        $notifierEmails = explode(',', $notifierEmails);
        foreach ($notifierEmails as $email) {
            try {
                $sender = [
                    'name' => $scopeConfig->getValue('trans_email/ident_general/name'),
                    'email' => $scopeConfig->getValue('trans_email/ident_general/email'),
                ];
                $transport = $transportBuilder
                    ->setTemplateIdentifier($scopeConfig->getValue('lowstocknotification/notification/send_email_notification'))
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $storeId
                        ]
                    )
                    ->setTemplateVars([
                        'productSystem' => $productSystem,
                        'productWarehouse' => $productWarehouse,
                        'createdAt' => date('Y-m-d')
                    ])
                    ->setFrom($sender)
                    ->addTo(trim($email))
                    ->getTransport();
                $transport->sendMessage();
            } catch (\Exception $e) {
                return;
            }
        }
    }

    /**
     * @param array $rule
     */
    public function startNotification($rule) {
        /** @var \Magestore\InventorySuccess\Model\LowStockNotification\NotificationManagement $notificationManagement */
        $notificationManagement = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\LowStockNotification\NotificationManagement'
        );
        /** @var \Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Rule\Product\CollectionFactory $ruleProductCollectionFactory */
        $ruleProductCollectionFactory = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Rule\Product\CollectionFactory'
        );
        $ruleProducts = $ruleProductCollectionFactory->create()
            ->addFieldToFilter('rule_id', $rule['rule_id']);
        if ($ruleProducts->getSize()) {
            $productSystem = $productWarehouse = '';
            $productIds = $ruleProducts->getColumnValues('product_id');
            $updateType = $rule['update_type'];
            if ($updateType == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_BOTH_SYSTEM_AND_WAREHOUSE) {
                $warehouseIds = $rule['warehouse_ids'];
                $productSystem = $notificationManagement->getProductNotificationBySystem($rule, $productIds);
                $productWarehouse = $notificationManagement->getProductNotificationByWarehouse($rule, $productIds, $warehouseIds);
            }
            if ($updateType == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_ONLY_WAREHOUSE) {
                $warehouseIds = $rule['warehouse_ids'];
                $productWarehouse = $notificationManagement->getProductNotificationByWarehouse($rule, $productIds, $warehouseIds);
            }
            if ($updateType == \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_ONLY_SYSTEM) {
                $productSystem = $notificationManagement->getProductNotificationBySystem($rule, $productIds);
            }
            if ($productSystem || $productWarehouse) {
                $this->sendEmailNotification($productSystem, $productWarehouse, $rule['notifier_emails']);
            }
        }
    }

    /**
     *
     */
    public function createDefaultNotificationRule()
    {
        $normalLevel = [
            'rule_name' => __('Normal level low stock notifications'),
            'lowstock_threshold' => 10,
            'sales_period' => 30,
            'from_date' => "",
            'to_date' => "",
            'status' => \Magestore\InventorySuccess\Model\LowStockNotification\Rule::STATUS_INACTIVE,
            'update_time_type' => \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TIME_TYPE_DAILY,
            'update_type' => \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_ONLY_SYSTEM,
            'specific_time' => '00',
            'description' => __('Normal level low stock notifications'),
            'notifier_emails' => '',
            'warning_message' => '',
            'conditions' => [
                1 => [
                    'type' => 'Magento\CatalogRule\Model\Rule\Condition\Combine',
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => ''
                ]
            ],
            'apply' => 0
        ];
        $highLevel = [
            'rule_name' => __('High level low stock notifications'),
            'lowstock_threshold' => 2,
            'sales_period' => 30,
            'from_date' => "",
            'to_date' => "",
            'status' => \Magestore\InventorySuccess\Model\LowStockNotification\Rule::STATUS_INACTIVE,
            'update_time_type' => \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TIME_TYPE_DAILY,
            'update_type' => \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_ONLY_SYSTEM,
            'specific_time' => '00',
            'description' => __('High level low stock notifications'),
            'notifier_emails' => '',
            'warning_message' => '',
            'conditions' => [
                1 => [
                    'type' => 'Magento\CatalogRule\Model\Rule\Condition\Combine',
                    'aggregator' => 'all',
                    'value' => '1',
                    'new_child' => ''
                ]
            ],
            'apply' => 0
        ];
        $data = [$normalLevel, $highLevel];
        foreach ($data as $levelNotify) {
            /** @var \Magestore\InventorySuccess\Model\LowStockNotification\Rule $ruleResourceModel */
            $ruleModel = \Magento\Framework\App\ObjectManager::getInstance()->create(
                '\Magestore\InventorySuccess\Model\LowStockNotification\Rule'
            );
            $ruleModel->addData($levelNotify);
            try {
                $ruleModel->save();
            } catch (\Exception $e) {
                return $this;
            }
        }
    }
}