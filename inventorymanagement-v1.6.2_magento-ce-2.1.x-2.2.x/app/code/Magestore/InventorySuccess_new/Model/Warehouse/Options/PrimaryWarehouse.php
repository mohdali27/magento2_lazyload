<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\Warehouse\Options;

/**
 * Country Options for Stores.
 *
 * @category Magestore
 * @package  Magestore_Inventoryplus
 * @module   Inventoryplus
 * @author   Magestore Developer
 */
class PrimaryWarehouse implements \Magento\Framework\Data\OptionSourceInterface
{

    const STATUS_IS_PRIMARY = 1;
    const STATUS_IS_NOT_PRIMARY = 0;

    /**
     * get available statuses.
     *
     * @return []
     */
    public static function getOptionHash()
    {
        return [
            self::STATUS_IS_PRIMARY => __('Yes')
            , self::STATUS_IS_NOT_PRIMARY => __('No'),
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
