<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Notification\Product;

/**
 * Collection Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(
            'Magestore\InventorySuccess\Model\LowStockNotification\Notification\Product',
            'Magestore\InventorySuccess\Model\ResourceModel\LowStockNotification\Notification\Product'
        );
    }
}