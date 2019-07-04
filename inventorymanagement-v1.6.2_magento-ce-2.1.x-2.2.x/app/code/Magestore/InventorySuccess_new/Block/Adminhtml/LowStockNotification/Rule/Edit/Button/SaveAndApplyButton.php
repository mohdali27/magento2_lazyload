<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Block\Adminhtml\LowStockNotification\Rule\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveAndApplyButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     * @codeCoverageIgnore
     */
    public function getButtonData()
    {
        if ($this->permissionManagementInterface->checkPermission('Magestore_InventorySuccess::view_notification_rule')) {
            $data = [];
            if ($this->canRender('save_apply')) {
                $data = [
                    'label' => __('Save and Apply'),
                    'class' => 'save',
                    'on_click' => '',
                    'sort_order' => 80,
                    'data_attribute' => [
                        'mage-init' => [
                            'Magento_Ui/js/form/button-adapter' => [
                                'actions' => [
                                    [
                                        'targetName' => 'os_lowstock_notification_rule_form.os_lowstock_notification_rule_form',
                                        'actionName' => 'save',
                                        'params' => [
                                            true,
                                            ['auto_apply' => 1],
                                        ]
                                    ]
                                ]
                            ]
                        ],

                    ]
                ];
            }
            return $data;
        }
    }
}
