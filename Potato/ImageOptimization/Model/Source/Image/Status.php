<?php

namespace Potato\ImageOptimization\Model\Source\Image;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Status
 */
class Status implements OptionSourceInterface
{
    const STATUS_PENDING   = 'pending';
    const STATUS_ERROR     = 'error';
    const STATUS_OPTIMIZED = 'optimized';
    const STATUS_PENDING_SERVICE   = 'pending_transfer_service';
    const STATUS_SERVICE   = 'transferred_service';
    const STATUS_OUTDATED  = 'outdated';
    const STATUS_SKIPPED   = 'skipped';
    
    /**
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::STATUS_PENDING => __("Pending"),
            self::STATUS_ERROR => __("Error"),
            self::STATUS_OPTIMIZED => __("Optimized"),
            self::STATUS_SERVICE => __("Transferred to the service"),
            self::STATUS_PENDING_SERVICE => __("Pending to transfer to the service"),
            self::STATUS_OUTDATED => __("Outdated"),
            self::STATUS_SKIPPED => __("Skipped"),
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
