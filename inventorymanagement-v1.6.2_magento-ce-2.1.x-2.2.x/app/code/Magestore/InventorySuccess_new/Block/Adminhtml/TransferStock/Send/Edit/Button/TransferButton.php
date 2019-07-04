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
class TransferButton extends \Magestore\InventorySuccess\Block\Adminhtml\TransferStock\AbstractTransferStock
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

            if($this->_permissionManagement->checkPermission(TransferPermission::SEND_STOCK_CREATE, $this->getWarehouse())){
                if($transferStock->getStatus() == TransferStock::STATUS_PENDING){
                    return [
                        'label' => __('Start Send Stock'),
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
                                                    'status' =>TransferStock::STATUS_PROCESSING,
                                                    'update_stock' =>true,
                                                    'action' => 'start_send'
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

            if($this->_permissionManagement->checkPermission(TransferPermission::SEND_STOCK_COMPLETE, $this->getWarehouse())){
                if($transferStock->getStatus() == TransferStock::STATUS_PROCESSING){
                    return [
                        'label' => __('Mark as Completed'),
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
                                                    'status' =>TransferStock::STATUS_COMPLETED,
                                                    'action' =>'complete'
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


        }

        return [];
    }
}
