<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\LowStockNotification\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class AbstractSource
 * @package Magestore\InventorySuccess\Model\LowStockNotification\Source
 */

abstract class AbstractSource implements OptionSourceInterface
{
    /**
     * @var \Magestore\InventorySuccess\Model\LowStockNotification\RuleFactory
     */
    protected $_lowStockNotificationRuleFactory;

    /**
     * @var \Magestore\InventorySuccess\Model\LowStockNotification\NotificationFactory
     */
    protected $_notificationFactory;

    /**
     * Warehouse constructor.
     * @param \Magestore\InventorySuccess\Model\SupplyNeeds $supplyNeeds
     */
    public function __construct(
        \Magestore\InventorySuccess\Model\LowStockNotification\RuleFactory $ruleFactory,
        \Magestore\InventorySuccess\Model\LowStockNotification\NotificationFactory $notificationFactory
    ) {
        $this->_lowStockNotificationRuleFactory = $ruleFactory;
        $this->_notificationFactory = $notificationFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [];
    }
}
