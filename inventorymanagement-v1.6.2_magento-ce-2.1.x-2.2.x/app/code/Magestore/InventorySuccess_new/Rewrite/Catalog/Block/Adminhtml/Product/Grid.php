<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magestore\InventorySuccess\Rewrite\Catalog\Block\Adminhtml\Product;

class Grid extends \Magento\Catalog\Block\Adminhtml\Product\Grid
{
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        parent::_prepareCollection();

        if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
            $this->getCollection()->getSelect()->where('at_qty.website_id=0');
        }
        return $this;
    }    
}
