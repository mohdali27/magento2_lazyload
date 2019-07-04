<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class Events implements ArrayInterface
{
    const SUCCESS = 'success';
    const UNSUCCESS = 'unsuccess';
    const NONE = 'none';

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $optionArray = [];
        $arr = $this->toArray();
        foreach ($arr as $value => $label) {
            $optionArray[] = [
                'value' => $value,
                'label' => $label
            ];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $options = [
            self::NONE => __('None'),
            self::SUCCESS => __('Successful Export'),
            self::UNSUCCESS => __('Unsuccessful Export')
        ];

        return $options;
    }
}
