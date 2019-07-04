<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\OrderProcess;

interface CreateCreditmemoInterface
{

    /**
     * execute the process
     * 
     * @param \Magento\Sales\Model\Order\Creditmemo\Item $item
     * @return bool
     */
    public function execute($item);
    
}