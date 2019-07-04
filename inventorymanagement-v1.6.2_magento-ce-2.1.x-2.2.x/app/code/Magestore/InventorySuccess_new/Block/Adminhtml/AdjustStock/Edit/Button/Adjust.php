<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Block\Adminhtml\AdjustStock\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magestore\InventorySuccess\Api\Data\AdjustStock\AdjustStockInterface;

/**
 * Class SaveButton
 */
class Adjust extends \Magestore\InventorySuccess\Block\Adminhtml\AdjustStock\AbstractAdjustStock
    implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        if($this->permissionManagementInterface
                ->checkPermission('Magestore_InventorySuccess::confirm_adjuststock', $this->getWarehouse())
            && $this->getAdjustStockStatus() != AdjustStockInterface::STATUS_COMPLETED
            && $this->getRequest()->getParam('id')) {
            return [
                'label' => __('Adjust'),
                'class' => 'save primary',
                'on_click' => '',
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'os_adjuststock_form.os_adjuststock_form',
                                    'actionName' => 'save',
                                    'params' => [
                                        true,
                                        [
                                            'back' => 'confirm'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'sort_order' => 20
            ];
        }
        return;
    }

}
