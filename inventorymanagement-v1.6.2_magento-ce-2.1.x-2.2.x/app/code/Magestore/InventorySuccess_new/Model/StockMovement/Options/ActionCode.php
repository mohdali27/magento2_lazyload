<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\StockMovement\Options;

/**
 * Class Status
 * @package Magestore\Inventoryplus\Model\Warehouse\Options
 */
class ActionCode implements \Magento\Framework\Option\ArrayInterface
{
    protected $_stockMovementProvider;
    
    public function __construct(
        \Magestore\InventorySuccess\Model\StockActivity\StockMovementProvider $stockMovementProvider
    ){
        $this->_stockMovementProvider = $stockMovementProvider;
    }

    /**
     * get available statuses.
     *
     * @return []
     */
    public function getOptionHash()
    {
        return $this->_stockMovementProvider->toActionOptionHash();
    }

    /**
     * get model option hash as array
     *
     * @return array
     */
    public function getOptionArray()
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
