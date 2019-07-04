<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\SupplyNeeds\Source;

/**
 * Class Warehouse
 * @package Magestore\InventorySuccess\Model\SupplyNeeds\Source
 */

class Warehouse extends AbstractSource
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->_supplyNeeds->getWarehouses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => (string)$value,
                'value' => (string)$key,
            ];
        }
        return $options;
    }
}
