<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Data\LowStockNotification;


interface NotificationInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const NOTIFICATION_ID = 'notification_id';
    const RULE_ID = 'rule_id';
    const CREATED_AT = 'created_at';
    const UPDATE_TYPE = 'update_type';
    const NOTIFIER_EMAILS = 'notifier_emails';
    const LOWSTOCK_THRESHOLD = 'lowstock_threshold';
    const SALES_PERIOD = 'sales_period';
    const WAREHOUSE_ID = 'warehouse_id';
    const WAREHOUSE_NAME = 'warehouse_name';
    const WARNING_MESSAGE = 'warning_message';

    /**#@-*/

    /**
     * Notification id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set notification id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Rule id
     *
     * @return int|null
     */
    public function getRuleId();

    /**
     * Set rule id
     *
     * @param int $ruleId
     * @return $this
     */
    public function setRuleId($ruleId);

    /**
     * Product created date
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set product created date
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Update type
     *
     * @return int|null
     */
    public function getUpdateType();

    /**
     * Set update type
     *
     * @param int $updateType
     * @return $this
     */
    public function setUpdateType($updateType);

    /**
     * Notifier emails
     *
     * @return string|null
     */
    public function getNotifierEmails();

    /**
     * Set notifier emails
     *
     * @param string $notifierEmails
     * @return $this
     */
    public function setNotifierEmails($notifierEmails);

    /**
     * Lowstock threshold
     *
     * @return int|null
     */
    public function getLowstockThreshold();

    /**
     * Set Lowstock threshold
     *
     * @param int $lowstockThreshold
     * @return $this
     */
    public function setLowstockThreshold($lowstockThreshold);

    /**
     * Sales Period
     *
     * @return int|null
     */
    public function getSalesPeriod();

    /**
     * Set Sales Period
     *
     * @param int $salesPeriod
     * @return $this
     */
    public function setSalesPeriod($salesPeriod);

    /**
     * Warehouse Id
     *
     * @return int|null
     */
    public function getWarehouseId();

    /**
     * Set Warehouse Id
     *
     * @param int $warehouseId
     * @return $this
     */
    public function setWarehouseId($warehouseId);

    /**
     * Warehouse Name
     *
     * @return string|null
     */
    public function getWarehouseName();

    /**
     * Set Warehouse Name
     *
     * @param string $warehouseName
     * @return $this
     */
    public function setWarehouseName($warehouseName);

    /**
     * Warning Message
     *
     * @return string|null
     */
    public function getWarningMessage();

    /**
     * Set Warning Message
     *
     * @param string $warningMessage
     * @return $this
     */
    public function setWarningMessage($warningMessage);
}
