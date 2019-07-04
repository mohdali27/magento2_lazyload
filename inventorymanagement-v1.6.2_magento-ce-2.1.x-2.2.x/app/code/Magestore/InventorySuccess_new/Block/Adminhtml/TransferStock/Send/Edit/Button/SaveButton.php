<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Block\Adminhtml\TransferStock\Send\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magestore\InventorySuccess\Model\TransferStock;
use Magestore\InventorySuccess\Api\Data\TransferStock\TransferPermission;

/**
 * Class SaveButton
 */
class SaveButton extends \Magestore\InventorySuccess\Block\Adminhtml\TransferStock\AbstractTransferStock
                 implements ButtonProviderInterface
{
    /**
     * Save button
     *
     * @return array
     */
    public function getButtonData()
    {
        if ($this->getRequest()->getParam('id')) {
            $transferStock = $this->_transferStockFactory->create()->load($this->getRequest()->getParam('id'));
            if(($transferStock->getStatus() == TransferStock::STATUS_PENDING) 
                    && $this->_permissionManagement->checkPermission(TransferPermission::SEND_STOCK_EDIT_GENERAL, $this->getWarehouse())){
                return [
                    'label' => __('Save'),
                    'class' => 'save primary',
                    'data_attribute' => [
                        'mage-init' => [
                            'buttonAdapter' => [
                                'actions' => [
                                    [
                                        'targetName' => 'os_transferstock_send_form.os_transferstock_send_form',
                                        'actionName' => 'save',
                                        'params' => [
                                            true,
                                            [
                                                'id' => $this->getRequest()->getParam('id'),
                                                'action' =>'save_general'
                                            ],

                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'sort_order' => 30,
                ];
            }
            else{
                return [];
            }



        }

        return [
            'label' => __('Prepare Product List'),
            'class' => 'save primary',
            'on_click' => '',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'os_transferstock_send_form.os_transferstock_send_form',
                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    [
                                        'id' => $this->getRequest()->getParam('id'),
                                        'status' =>TransferStock::STATUS_PENDING,
                                        'action' =>'prepare_product'
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'sort_order' => 30,
        ];
    }
}
