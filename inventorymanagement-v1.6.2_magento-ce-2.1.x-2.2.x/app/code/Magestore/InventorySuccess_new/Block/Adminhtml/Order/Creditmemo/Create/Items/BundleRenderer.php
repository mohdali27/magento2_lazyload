<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Order\Creditmemo\Create\Items;

class BundleRenderer extends \Magento\Bundle\Block\Adminhtml\Sales\Order\Items\Renderer
{
    /**
     * is Auto Return enabled
     * 
     * @return bool
     */
    public function isAutoReturn()
    {
        return $this->stockConfiguration->isAutoReturnEnabled();        
    }
}