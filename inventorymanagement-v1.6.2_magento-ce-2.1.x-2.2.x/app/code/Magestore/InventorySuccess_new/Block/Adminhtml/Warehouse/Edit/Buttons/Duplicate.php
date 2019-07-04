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
class Duplicate extends Generic
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        if (!$this->getWarehouse() || !$this->getWarehouse()->getId())
            return [];

        if(!$this->_permissionManagement->checkPermission(
            'Magestore_InventorySuccess::warehouse_duplicate',$this->getWarehouse())){
            return [];
        }

        $url = $this->getUrl('*/*/duplicate', ['id' => $this->getWarehouse()->getId()]);
        return [
            'label' => __('Duplicate Location'),
            'class' => 'delete',
            'on_click' => sprintf("deleteConfirm(
                    'Are you sure you want to duplicate this location?', 
                    '%s'
                )", $url),
            'sort_order' => 20
        ];
    }
}
