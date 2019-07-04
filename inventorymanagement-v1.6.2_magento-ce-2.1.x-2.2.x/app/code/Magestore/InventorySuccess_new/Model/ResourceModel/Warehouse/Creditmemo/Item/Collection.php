<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Creditmemo\Item;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Magestore\InventorySuccess\Model\Warehouse\Creditmemo\Item',
            'Magestore\InventorySuccess\Model\ResourceModel\Warehouse\Creditmemo\Item'
        );
    }
}