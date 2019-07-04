<?php

namespace Potato\ImageOptimization\Model\Source\System;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class OptimizationMethod
 */
class OptimizationMethod implements OptionSourceInterface
{
    const USE_SERVICE   = 'service';
    const USE_SERVER_APP = 'server';

    /**
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::USE_SERVER_APP => __("Self-hosted server applications by cron"),
            self::USE_SERVICE => __("PotatoCommerce Image Optimization Service")
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
