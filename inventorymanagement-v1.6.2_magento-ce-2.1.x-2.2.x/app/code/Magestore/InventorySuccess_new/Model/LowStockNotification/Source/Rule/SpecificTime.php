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
class SpecificTime extends \Magestore\InventorySuccess\Model\LowStockNotification\Source\AbstractSource implements OptionSourceInterface
{

    /**
     * Get options
     *
     * @return array
     */

    public function toOptionArray()
    {
        for($i = 0;$i<=23;$i++){
            $i = sprintf("%02d", $i);
            $times[$i]= $i.':00';
        }
        $hours = [];
        foreach ($times as $id=>$value) {
            $hours[] =['value'=>(string)$id, 'label'=>$value];
        }
        return $hours;
    }
}
