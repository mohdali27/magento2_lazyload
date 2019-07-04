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
class Start extends \Magestore\InventorySuccess\Block\Adminhtml\Stocktaking\AbstractStocktaking
    implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        if(!$this->getRequest()->getParam('id')
            || $this->getStocktakingStatus() == Stocktaking::STATUS_PENDING)
            return [
                'label' => __('Start stocktake'),
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
                                            'back' => 'start'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'sort_order' => 50,
            ];

        if($this->getStockTakingStatus() == Stocktaking::STATUS_COMPLETED)
            return [
                'label' => __('Export Difference List'),
                'class' => 'save primary',
                'on_click' => sprintf(
                    "location.href = '%s';",
                    $this->getUrl(
                        '*/*/export',
                        [
                            '_secure' => true,
                            'id' => $this->getRequest()->getParam('id'),
                            'status' => $this->getStocktakingStatus(),
                            'different' => 'true'
                        ]
                    )
                ),
                'sort_order' => 50,
            ];
        if($this->getStockTakingStatus() == Stocktaking::STATUS_PROCESSING)
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
                                            'back' => 'start'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'sort_order' => 50,
            ];

        if($this->getStockTakingStatus() == Stocktaking::STATUS_VERIFIED
         ||$this->getStockTakingStatus() == Stocktaking::STATUS_COMPLETED)
            return [
                'label' => __('Export Counted Products'),
                'class' => 'save primary',
                'on_click' => sprintf(
                    "location.href = '%s';",
                    $this->getUrl(
                        '*/*/export',
                        [
                            '_secure' => true,
                            'id' => $this->getRequest()->getParam('id'),
                            'status' => $this->getStocktakingStatus()
                        ]
                    )
                ),
                'sort_order' => 50,
            ];
    }

}
