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
class Verify extends \Magestore\InventorySuccess\Block\Adminhtml\Stocktaking\AbstractStocktaking
    implements ButtonProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        if (!$this->getRequest()->getParam('id')) {
            return [
                'label' => __('Prepare Product List'),
                'class' => 'primary',
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
                    ],
                    'form-role' => 'save',
                ],
                'sort_order' => 10,
            ];
        }
        if ($this->getStockTakingStatus() == Stocktaking::STATUS_PENDING) {
            return [
                'label' => __('Export Products'),
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
                'sort_order' => 10
            ];
        }
        if ($this->getStockTakingStatus() == Stocktaking::STATUS_COMPLETED
            && $this->permissionManagementInterface
                ->checkPermission('Magestore_InventorySuccess::create_adjuststock', $this->getWarehouse())
        ) {
            $id = '';
            $warehouseId = '';
            $stocktakingCode = '';
            if ($this->getStocktaking()) {
                $id = $this->getStocktaking()->getId();
                $warehouseId = $this->getStocktaking()->getData('warehouse_id');
                $stocktakingCode = $this->getStocktaking()->getData('stocktaking_code');
            }
            $url = $this->getUrl('*/*/adjust',
                array(
                    '_secure' => true,
                    'id' => $id,
                    'warehouse_id' => $warehouseId,
                    'stocktaking_code' => $stocktakingCode
                ));
            return [
                'label' => __('Adjust Stock'),
                'on_click' => sprintf("deleteConfirm(
                        'Are you sure you want to adjust stock', 
                        '%s'
                    )", $url),
                'class' => 'primary',
                'sort_order' => 10
            ];

        }
        if ($this->getStockTakingStatus() == Stocktaking::STATUS_PROCESSING
            && $this->permissionManagementInterface
                ->checkPermission('Magestore_InventorySuccess::verify_stocktaking', $this->getWarehouse())
        ) {
            return [
                'label' => __('Complete Data Entry'),
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
                                            'back' => 'verify'
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
        if ($this->getStockTakingStatus() == Stocktaking::STATUS_VERIFIED) {
            return [
                'label' => __('Re-Data Entry'),
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
                                            'back' => 'redata'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'sort_order' => 10
            ];
        }
    }

}
