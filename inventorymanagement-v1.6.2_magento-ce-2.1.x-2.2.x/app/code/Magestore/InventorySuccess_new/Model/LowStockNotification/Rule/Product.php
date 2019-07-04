<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Created by PhpStorm.
 * User: zero
 * Date: 06/04/2016
 * Time: 09:29
 */

namespace Magestore\InventorySuccess\Model\LowStockNotification\Rule;

class Product extends \Magento\Framework\Model\AbstractModel
    implements \Magestore\InventorySuccess\Api\Data\LowStockNotification\Rule\ProductInterface
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Rule\Product');
    }
}