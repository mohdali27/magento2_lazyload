<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Block\Adminhtml\Stocktaking\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Ui\Component\Control\Container;
use Magestore\InventorySuccess\Model\Stocktaking;

/**
 * Class SaveButton
 */
class Confirm extends \Magestore\InventorySuccess\Block\Adminhtml\Stocktaking\AbstractStocktaking
           implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        if($this->getStocktakingStatus() != null && $this->getStocktakingStatus() == Stocktaking::STATUS_PENDING)
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
                'sort_order' => 60,
                'class_name' => Container::SPLIT_BUTTON,
                'options' => $this->getOptions(),
            ];
        if($this->getStockTakingStatus() == Stocktaking::STATUS_COMPLETED)
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
                'sort_order' => 30,
            ];
        if(($this->getStockTakingStatus() == Stocktaking::STATUS_PROCESSING
            || $this->getStockTakingStatus() == Stocktaking::STATUS_VERIFIED)
        && $this->permissionManagementInterface
                ->checkPermission('Magestore_InventorySuccess::confirm_stocktaking', $this->getWarehouse()))
            return [
                'label' => __('Complete Stocktake'),
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
                                            'back' => 'confirm'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'sort_order' => 30
            ];
    }

    /**
     * Retrieve options
     *
     * @return array
     */
    protected function getOptions()
    {
        $options[] = [
            'id_hard' => 'save_and_new',
            'label' => __('Save & New'),
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
                                        'back' => 'new'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        $options[] = [
            'id_hard' => 'save_and_close',
            'label' => __('Save & Close'),
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
                                        'back' => 'close'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];

        return $options;
    }
}
