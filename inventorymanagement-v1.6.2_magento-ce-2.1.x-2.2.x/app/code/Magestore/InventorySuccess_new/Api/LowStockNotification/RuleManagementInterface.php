<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\LowStockNotification;


interface RuleManagementInterface
{
    /**
     * @param \Magestore\InventorySuccess\Model\LowStockNotification\Rule $ruleModel
     * @return string
     */
    public function getNewNextTime($ruleModel);

    /**
     * Get array of product ids which are matched by rule
     * @param \Magestore\InventorySuccess\Model\LowStockNotification\Rule $ruleModel
     * @return array
     */
    public function getListProductIdsInRule($ruleModel);

    /**
     * @return array
     */
    public function getAvailableRules();

    /**
     * @param $productSystem
     * @param $productWarehouse
     * @param $notifierEmails
     */
    public function sendEmailNotification($productSystem, $productWarehouse, $notifierEmails);

    /**
     * @return mixed
     */
    public function createDefaultNotificationRule();
}