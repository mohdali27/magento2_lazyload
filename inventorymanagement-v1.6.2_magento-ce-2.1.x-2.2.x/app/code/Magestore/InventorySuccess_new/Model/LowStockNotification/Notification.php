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

class Notification extends \Magento\Framework\Model\AbstractModel
    implements \Magestore\InventorySuccess\Api\Data\LowStockNotification\NotificationInterface
{
    /**
     * Notification's update type
     */
    const TYPE_SYSTEM = 1;
    const TYPE_WAREHOUSE = 2;

    /**
     * notification type
     */
    const NOTIFY_TYPE_SYSTEM = 1;
    const NOTIFY_TYPE_WAREHOUSE = 2;

    /**
     * Model construct that should be used for object initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Notification');
    }

    /**
     * Prepare notification's update type.
     *
     * @return array
     */
    public function getAvailableUpdateType()
    {
        return [
            self::TYPE_SYSTEM => __('System'),
            self::TYPE_WAREHOUSE => __('Location')
        ];
    }

    /**
     * Notification id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::NOTIFICATION_ID);
    }

    /**
     * Set notification id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::NOTIFICATION_ID, $id);
    }

    /**
     * Rule id
     *
     * @return int|null
     */
    public function getRuleId()
    {
        return $this->getData(self::RULE_ID);
    }

    /**
     * Set rule id
     *
     * @param int $ruleId
     * @return $this
     */
    public function setRuleId($ruleId)
    {
        return $this->setData(self::RULE_ID, $ruleId);
    }

    /**
     * Product created date
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set product created date
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Update type
     *
     * @return int|null
     */
    public function getUpdateType()
    {
        return $this->getData(self::UPDATE_TYPE);
    }

    /**
     * Set update type
     *
     * @param int $updateType
     * @return $this
     */
    public function setUpdateType($updateType)
    {
        return $this->setData(self::UPDATE_TYPE, $updateType);
    }

    /**
     * Notifier emails
     *
     * @return string|null
     */
    public function getNotifierEmails()
    {
        return $this->getData(self::NOTIFIER_EMAILS);
    }

    /**
     * Set notifier emails
     *
     * @param string $notifierEmails
     * @return $this
     */
    public function setNotifierEmails($notifierEmails)
    {
        return $this->setData(self::NOTIFIER_EMAILS, $notifierEmails);
    }

    /**
     * Lowstock threshold
     *
     * @return int|null
     */
    public function getLowstockThreshold()
    {
        return $this->getData(self::LOWSTOCK_THRESHOLD);
    }

    /**
     * Set Lowstock threshold
     *
     * @param int $lowstockThreshold
     * @return $this
     */
    public function setLowstockThreshold($lowstockThreshold)
    {
        return $this->setData(self::LOWSTOCK_THRESHOLD, $lowstockThreshold);
    }

    /**
     * Sales Period
     *
     * @return int|null
     */
    public function getSalesPeriod()
    {
        return $this->getData(self::SALES_PERIOD);
    }

    /**
     * Set Sales Period
     *
     * @param int $salesPeriod
     * @return $this
     */
    public function setSalesPeriod($salesPeriod)
    {
        return $this->setData(self::SALES_PERIOD, $salesPeriod);
    }

    /**
     * Warehouse Id
     *
     * @return int|null
     */
    public function getWarehouseId()
    {
        return $this->getData(self::WAREHOUSE_ID);
    }

    /**
     * Set Warehouse Id
     *
     * @param int $warehouseId
     * @return $this
     */
    public function setWarehouseId($warehouseId)
    {
        return $this->setData(self::WAREHOUSE_ID, $warehouseId);
    }

    /**
     * Warehouse Name
     *
     * @return string|null
     */
    public function getWarehouseName()
    {
        return $this->getData(self::WAREHOUSE_NAME);
    }

    /**
     * Set Warehouse Name
     *
     * @param string $warehouseName
     * @return $this
     */
    public function setWarehouseName($warehouseName)
    {
        return $this->setData(self::WAREHOUSE_NAME, $warehouseName);
    }

    /**
     * Warning Message
     *
     * @return string|null
     */
    public function getWarningMessage()
    {
        return $this->getData(self::WARNING_MESSAGE);
    }

    /**
     * Set Warning Message
     *
     * @param string $warningMessage
     * @return $this
     */
    public function setWarningMessage($warningMessage)
    {
        return $this->setData(self::WARNING_MESSAGE, $warningMessage);
    }
}