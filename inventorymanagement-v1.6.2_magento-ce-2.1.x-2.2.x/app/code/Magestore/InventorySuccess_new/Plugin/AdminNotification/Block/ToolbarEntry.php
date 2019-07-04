<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Plugin\AdminNotification\Block;

/**
 *
 *
 * @category Magestore
 * @package  Magestore_Inventorywarehouse
 * @module   Inventorywarehouse
 * @author   Magestore Developer
 */
class ToolbarEntry
{
    /**
     * @param \Magento\AdminNotification\Block\ToolbarEntry $toolbarEntry
     * @param $template
     * @return string
     */
    public function afterGetTemplate(\Magento\AdminNotification\Block\ToolbarEntry $toolbarEntry, $template)
    {
        $template = 'Magestore_InventorySuccess::lowstocknotification/notification/toolbar_entry.phtml';
        return $template;
    }
}