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
class SpecificDay extends \Magestore\InventorySuccess\Model\LowStockNotification\Source\AbstractSource implements OptionSourceInterface
{

    /**
     * Get options
     *
     * @return array
     */

    public function toOptionArray()
    {
        $days = [];
        for ($i=1;$i<=31;$i++) {
            $days[] = ['value'=> (string)$i, 'label'=>sprintf("%02d", $i)];
        }
        return $days;
    }
}
