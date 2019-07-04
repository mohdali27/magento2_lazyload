<?php

namespace Potato\ImageOptimization\Model\Source\Optimization;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Error
 */
class Error implements OptionSourceInterface
{
    const IS_NOT_READABLE   = 'is_not_readable';
    const CANT_UPDATE     = 'cant_update';
    const STATIC_CANT_UPDATE = 'static_cant_update';
    const BACKUP_CREATION   = 'backup_creation';
    const UNSUPPORTED_IMAGE  = 'unsupported_image';
    const APPLICATION   = 'application';
    const TEMP_CREATION = 'temp_creation';

    /**
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::IS_NOT_READABLE => __("File is not readable"),
            self::CANT_UPDATE => __("File can't be updated"),
            self::STATIC_CANT_UPDATE => __("Static file can't be updated"),
            self::BACKUP_CREATION => __("Can't create a backup"),
            self::UNSUPPORTED_IMAGE => __("Unsupported image type"),
            self::APPLICATION => __("Application error"),
            self::TEMP_CREATION => __("Incorrect temp file")
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

    /**
     * @param string $errorCode
     * @return string|null
     */
    public function getLabelByCode($errorCode)
    {
        $options = $this->getOptionArray();
        if (isset($options[$errorCode])) {
            return $options[$errorCode];
        }
        return null;
    }
}
