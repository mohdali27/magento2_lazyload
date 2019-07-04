<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Mode implements ArrayInterface
{
    const MANUALLY  = 'manual';
    const HOURLY    = 'hourly';
    const DAILY     = 'daily';
    const WEEKLY    = 'weekly';
    const MONTHLY   = 'monthly';

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            self::MANUALLY  => __('Manually'),
            self::HOURLY    => __('Hourly'),
            self::DAILY     => __('Daily'),
            self::WEEKLY    => __('Weekly'),
            self::MONTHLY   => __('Monthly'),
        ];
    }
}