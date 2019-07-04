<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Order\Shipment\Create\Items;

class BundleRenderer extends \Magento\Bundle\Block\Adminhtml\Sales\Order\Items\Renderer
{
    /**
     * 
     * @param \Magento\Framework\DataObject $item
     * @return float
     */
    public function getBundleQty($item)
    {
        $attributes = $this->getSelectionAttributes($item);
        
        if(isset($attributes['qty'])) {
            return $attributes['qty'];
        }
        return 0;
    }
}