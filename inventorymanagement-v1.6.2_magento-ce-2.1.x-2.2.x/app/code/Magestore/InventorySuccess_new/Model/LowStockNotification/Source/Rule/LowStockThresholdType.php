<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\LowStockNotification\Source\Rule;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 */
class LowStockThresholdType extends \Magestore\InventorySuccess\Model\LowStockNotification\Source\AbstractSource implements OptionSourceInterface
{

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->_lowStockNotificationRuleFactory->create()->getAvailableLowStockThresholdType();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
