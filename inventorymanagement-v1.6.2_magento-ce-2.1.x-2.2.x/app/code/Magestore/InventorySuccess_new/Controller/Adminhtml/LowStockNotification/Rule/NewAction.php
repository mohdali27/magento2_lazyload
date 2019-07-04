<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\Rule;

class NewAction extends \Magestore\InventorySuccess\Controller\Adminhtml\LowStockNotification\AbstractLowStockNotification
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
