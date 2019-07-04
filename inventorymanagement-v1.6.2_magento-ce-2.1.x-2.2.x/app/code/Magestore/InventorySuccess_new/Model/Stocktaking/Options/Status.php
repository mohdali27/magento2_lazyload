<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Stocktaking\Options;

/**
 * Class Status
 * @package Magestore\InventorySuccess\Model\Stocktaking\Options
 */
class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs.
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray() {
        $option = [];

        $option[] = ['label' => __('Pending'), 'value' => \Magestore\InventorySuccess\Model\Stocktaking::STATUS_PENDING];
        $option[] = ['label' => __('Completed'), 'value' => \Magestore\InventorySuccess\Model\Stocktaking::STATUS_COMPLETED];
        $option[] = ['label' => __('Processing'), 'value' => \Magestore\InventorySuccess\Model\Stocktaking::STATUS_PROCESSING];
        $option[] = ['label' => __('Verified'), 'value' => \Magestore\InventorySuccess\Model\Stocktaking::STATUS_VERIFIED];
        $option[] = ['label' => __('Canceled'), 'value' => \Magestore\InventorySuccess\Model\Stocktaking::STATUS_CANCELED];

        return $option;
    }

    /**
     * Return array of options as key-value pairs.
     *
     * @return array Format: array('<key>' => '<value>', '<key>' => '<value>', ...)
     */
    public function toOptionHash() {
        $option = [
            \Magestore\InventorySuccess\Model\Stocktaking::STATUS_PENDING => __('Pending'),
            \Magestore\InventorySuccess\Model\Stocktaking::STATUS_COMPLETED => __('Complete'),
            \Magestore\InventorySuccess\Model\Stocktaking::STATUS_PROCESSING => __('Processing'),
            \Magestore\InventorySuccess\Model\Stocktaking::STATUS_VERIFIED => __('Verified'),
            \Magestore\InventorySuccess\Model\Stocktaking::STATUS_CANCELED => __('Canceled')
        ];

        return $option;
    }
}
