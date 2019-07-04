<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Catalog\Product\Attribute;

class Js extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magestore_InventorySuccess::catalog/product/attribute/js.phtml';
    
    /**
     * 
     * @return string
     */
    public function getQtyMessage()
    {
        $adjustStockUrl = $this->getUrl('inventorysuccess/adjuststock/new');
        $adjustStockUrl = '<a target=\"_blank\" href=\"'. $adjustStockUrl .'\">'.__('Stock Adjusting').'</a>';
        return __('Cannot edit directly product qty! You can update qty of products by %1', $adjustStockUrl);
    }

}