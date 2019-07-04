<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\OrderProcess;

interface CancelOrderInterface
{
    /**
     * execute the process
     * 
     * @param \Magento\Sales\Model\Order\Item $item
     * @return bool
     */
    public function execute($item);
}