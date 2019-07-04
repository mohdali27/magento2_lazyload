<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Warehouse\Options;


/**
 * Class Status
 * @package Magestore\Inventoryplus\Model\Warehouse\Options
 */
class Status implements \Magento\Framework\Option\ArrayInterface
{

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * get available statuses.
     *
     * @return []
     */
    public static function getOptionHash()
    {
        return [
            self::STATUS_ENABLED => __('Enabled')
            , self::STATUS_DISABLED => __('Disabled'),
        ];
    }

    /**
     * get model option hash as array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        $options = array();
        foreach (self::getOptionHash() as $value => $label) {
            $options[] = array(
                'value'    => $value,
                'label'    => $label
            );
        }
        return $options;
    }
    
    /**
     * Return array of options as value-label pairs.
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return self::getOptionArray();
    }

    /**
     * Return array of options as key-value pairs.
     *
     * @return array Format: array('<key>' => '<value>', '<key>' => '<value>', ...)
     */
    public function toOptionHash()
    {
        return self::getOptionHash();
    }
}
