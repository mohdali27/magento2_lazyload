<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Controller\Adminhtml\Warehouse\Product\Delete;

class Grid extends \Magestore\InventorySuccess\Controller\Adminhtml\ManageStock\Product\Grid
{
    protected $_stockGrid = 'Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Tab\DeleteProduct\Grid';
    protected $_stockGridName = 'warehouse.delete.product.grid';

}