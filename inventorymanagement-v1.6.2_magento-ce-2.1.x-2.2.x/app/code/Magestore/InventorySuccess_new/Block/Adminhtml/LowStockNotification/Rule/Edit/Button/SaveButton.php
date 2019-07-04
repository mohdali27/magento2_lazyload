<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\LowStockNotification\Rule\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function getButtonData()
    {
        if ($this->permissionManagementInterface->checkPermission('Magestore_InventorySuccess::view_notification_rule')) {
            $data = [];
            if ($this->canRender('save')) {
                $data = [
                    'label' => __('Save'),
                    'class' => 'save primary',
                    'on_click' => '',
                ];
            }
            return $data;
        }
    }
}
