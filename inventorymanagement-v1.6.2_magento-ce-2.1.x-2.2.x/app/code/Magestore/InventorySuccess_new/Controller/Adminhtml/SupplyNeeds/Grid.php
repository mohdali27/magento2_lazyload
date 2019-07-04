<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\SupplyNeeds;

/**
 * @category Magestore
 * @package  Magestore_Inventoryplus
 * @module   Inventoryplus
 * @author   Magestore Developer
 */
class Grid extends \Magestore\InventorySuccess\Controller\Adminhtml\SupplyNeeds\AbstractSupplyNeeds
{
    public function execute()
    {
        $resultLayout = $this->_resultLayoutFactory->create();
        return $resultLayout;
    }
}