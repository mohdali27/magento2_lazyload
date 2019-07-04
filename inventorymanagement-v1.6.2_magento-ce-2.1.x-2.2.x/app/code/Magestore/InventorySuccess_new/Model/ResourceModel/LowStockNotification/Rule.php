<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification;

/**
 * Resource Model Supplier
 */
class Rule extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('os_lowstock_notification_rule','rule_id');
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function getAvailableRules()
    {
        /** @var \Magento\Framework\Stdlib\DateTime\DateTime $dateTime */
        $dateTime = \Magento\Framework\App\ObjectManager::getInstance()->create(
            '\Magento\Framework\Stdlib\DateTime\DateTime'
        );
        $now = $dateTime->gmtDate();
        $status = \Magestore\InventorySuccess\Model\LowStockNotification\Rule::STATUS_ACTIVE;
        $typeLowStockThresholdByProductQty = \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_LOWSTOCK_THRESHOLD_PRODUCT_QTY;
        $typeLowStockThresholdBySaleDays = \Magestore\InventorySuccess\Model\LowStockNotification\Rule::TYPE_LOWSTOCK_THRESHOLD_SALE_DAY;
        $select = $this->getConnection()->select()->from(['main_table' => $this->getMainTable()]);
        $orWhereConditions = [
            "(main_table.from_date <= '{$now}' and main_table.to_date >= '{$now}')",
            "(main_table.from_date <= '{$now}' and main_table.to_date is null)",
            "(main_table.from_date is null and main_table.to_date >= '{$now}')",
            "(main_table.from_date is null and main_table.to_date is null)"
        ];
        $orWhereConditions1 = [
            "(main_table.lowstock_threshold_type = '{$typeLowStockThresholdByProductQty}' and main_table.lowstock_threshold_qty > 0)",
            "(main_table.lowstock_threshold_type = '{$typeLowStockThresholdBySaleDays}' and main_table.lowstock_threshold > 0 and main_table.sales_period > 0)"
        ];
        $andWhereConditions = [
            "main_table.status = '{$status}'",
            "main_table.next_time_action <= '{$now}'"
        ];
        $orWhereCondition = implode(' OR ', $orWhereConditions);
        $orWhereCondition1 = implode(' OR ', $orWhereConditions1);
        $andWhereCondition = implode(' AND ', $andWhereConditions);
        $select->where('(' . $orWhereCondition . ') AND (' . $orWhereCondition1 . ') AND ' . $andWhereCondition);
        $result = $this->getConnection()->fetchAll($select);
        return $result;
    }
}