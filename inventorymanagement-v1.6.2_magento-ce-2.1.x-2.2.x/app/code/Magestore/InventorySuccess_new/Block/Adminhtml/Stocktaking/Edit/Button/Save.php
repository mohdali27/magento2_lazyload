<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Block\Adminhtml\Stocktaking\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magestore\InventorySuccess\Model\Stocktaking;

/**
 * Class SaveButton
 */
class Save extends \Magestore\InventorySuccess\Block\Adminhtml\Stocktaking\AbstractStocktaking
    implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        if ($this->getStockTakingStatus() == Stocktaking::STATUS_COMPLETED) {
            return [
                'label' => __('Save'),
                'class' => 'save primary',
                'on_click' => '',
                'data_attribute' => [
                    'mage-init' => [
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'os_stocktaking_form.os_stocktaking_form',
                                    'actionName' => 'save',
                                    'params' => [
                                        true,
                                        [
                                            'back' => 'edit'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'sort_order' => 70
            ];
        }
    }
}
