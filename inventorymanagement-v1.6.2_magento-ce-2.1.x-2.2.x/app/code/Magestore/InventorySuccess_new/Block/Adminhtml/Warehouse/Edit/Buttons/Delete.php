<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Buttons;

use Magento\Ui\Component\Control\Container;

/**
 * Class Delete
 * @package Magestore\InventorySuccess\Block\Adminhtml\Warehouse\Edit\Buttons
 */
class Delete extends Generic
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        if (!$this->getWarehouse() || !$this->getWarehouse()->getId()) 
            return [];

        if(!$this->_permissionManagement->checkPermission(
            'Magestore_InventorySuccess::warehouse_delete',$this->getWarehouse())){
            return [];
        }

        $product = $this->warehouseInterface->getStockActivityProductModel()->getCollection()
            ->getTotalQtysFromWarehouse($this->getWarehouse()->getId());
        if($product->getSumTotalQty()>0 || $product->getSumQtyToShip()>0)
            return [];
        
        $url = $this->getUrl('*/*/delete', ['id' => $this->getWarehouse()->getId()]);
        return [
            'label' => __('Delete Location'),
            'class' => 'delete',
            'on_click' => sprintf("deleteConfirm(
                    'Are you sure you want to delete this location?', 
                    '%s'
                )", $url),
        ];
    }
}
