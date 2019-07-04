<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Plugin\OrderProcess;

class BeforeCreditmemoViewItems {

    public function beforeGetItemRenderer(\Magento\Sales\Block\Adminhtml\Order\Creditmemo\View\Items $items, $type) 
    {
        if($type == \Magento\Bundle\Model\Product\Type::TYPE_CODE) {
            $type = 'inventorysuccess_'.$type;
        } else {
            $type = 'inventorysuccess_default';
        }
        return [$type];
    }

}
