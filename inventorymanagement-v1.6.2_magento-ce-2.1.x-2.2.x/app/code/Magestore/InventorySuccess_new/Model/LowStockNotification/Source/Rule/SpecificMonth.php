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
class SpecificMonth extends \Magestore\InventorySuccess\Model\LowStockNotification\Source\AbstractSource implements OptionSourceInterface
{

    /**
     * Get options
     *
     * @return array
     */

    public function toOptionArray()
    {
        $months = [
            '1' => __('January'),
            '2' => __('February'),
            '3' => __('March'),
            '4' => __('April'),
            '5' => __('May'),
            '6' => __('June'),
            '7' => __('July'),
            '8' => __('August'),
            '9' => __('September'),
            '10' => __('October'),
            '11' => __('November'),
            '12' => __('December')
        ];
        $options = [];
        foreach ($months as $id => $value) {
            $options[] =  ['value' => (string)$id, 'label' => $value];
        }
        return $options;
    }
}
