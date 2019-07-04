<?php

namespace Potato\ImageOptimization\Model\Source\System;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class ImageQuality
 */
class ImageQuality implements OptionSourceInterface
{
    const QUALITY_100 = '-m100';
    const QUALITY_95 = '-m95';
    const QUALITY_90 = '-m90';
    const QUALITY_85 = '-m85';
    const QUALITY_80 = '-m80';
    const QUALITY_75 = '-m75';
    const QUALITY_70 = '-m70';
    const QUALITY_65 = '-m65';
    const QUALITY_60 = '-m60';
    const QUALITY_55 = '-m55';
    const QUALITY_50 = '-m50';
    const QUALITY_45 = '-m45';
    const QUALITY_40 = '-m40';
    const QUALITY_35 = '-m35';
    const QUALITY_30 = '-m30';

    /**
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::QUALITY_100 => __("Lossless optimization (default)"),
            self::QUALITY_95 => __("95%"),
            self::QUALITY_90 => __("90%"),
            self::QUALITY_85 => __("85%"),
            self::QUALITY_80 => __("80%"),
            self::QUALITY_75 => __("75%"),
            self::QUALITY_70 => __("70%"),
            self::QUALITY_65 => __("65%"),
            self::QUALITY_60 => __("60%"),
            self::QUALITY_55 => __("55%"),
            self::QUALITY_50 => __("50%"),
            self::QUALITY_45 => __("45%"),
            self::QUALITY_40 => __("40%"),
            self::QUALITY_35 => __("35%"),
            self::QUALITY_30 => __("30%"),
        ];
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->getOptionArray();
        $result = [];
        foreach ($options as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }
        return $result;
    }
}
