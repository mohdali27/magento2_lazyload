<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Model\ResourceModel\TransferStock;

use \Magestore\InventorySuccess\Model\ResourceModel\AbstractCollection;


class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'transferstock_id';



    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magestore\InventorySuccess\Model\TransferStock', 'Magestore\InventorySuccess\Model\ResourceModel\TransferStock');
    }

}
