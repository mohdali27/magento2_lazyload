<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Block\Adminhtml\LowStockNotification\Rule\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class DeleteButton
 */
class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        $data = [];
        if ($this->permissionManagementInterface->checkPermission('Magestore_InventorySuccess::view_notification_rule')) {
            $ruleId = $this->getRuleId();
            if ($ruleId && $this->canRender('delete')) {
                $data = [
                    'label' => __('Delete Rule'),
                    'class' => 'delete',
                    'on_click' => 'deleteConfirm(\'' . __(
                            'Are you sure you want to do this?'
                        ) . '\', \'' . $this->urlBuilder->getUrl('*/*/delete', ['id' => $ruleId]) . '\')',
                    'sort_order' => 20,
                ];
            }
        }
        return $data;
    }
}
