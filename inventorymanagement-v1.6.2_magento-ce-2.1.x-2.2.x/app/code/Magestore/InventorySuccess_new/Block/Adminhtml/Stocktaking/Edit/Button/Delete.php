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
class Delete extends \Magestore\InventorySuccess\Block\Adminhtml\Stocktaking\AbstractStocktaking
    implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        if ($this->getRequest()->getParam('id') && $this->getStockTakingStatus() == Stocktaking::STATUS_CANCELED) {
            return [
                'label' => __('Delete'),
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
                                            'back' => 'delete'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'form-role' => 'save',
                ],
                'sort_order' => 100,
            ];
        }
    }
}
