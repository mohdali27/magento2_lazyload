<?php
namespace Potato\Compressor\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class LazyLoad implements ArrayInterface
{
    const DO_NOT_USE_VALUE = 0;
    const LOAD_ALL_VALUE = 1;
    const LOAD_VISIBLE_VALUE = 2;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->toArray() as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }
        return $result;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::LOAD_ALL_VALUE => __("Load load image(s) after page load"),
            self::LOAD_VISIBLE_VALUE => __("Load image(s) when it is needed"),
            self::DO_NOT_USE_VALUE => __("No"),
        ];
    }
}
