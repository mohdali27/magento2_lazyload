<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Path implements ArrayInterface
{
    const USE_DEFAULT = 0;
    const USE_SHORTEST = 1;
    const USE_LONGEST = 2;

    public function toOptionArray()
    {
        return [
            ['value' => self::USE_DEFAULT, 'label' => __('Default Rules')],
            ['value' => self::USE_SHORTEST, 'label' => __('Shortest Path')],
            ['value' => self::USE_LONGEST, 'label' => __('Longest Path')],
        ];
    }
}
