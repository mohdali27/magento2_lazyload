<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magestore\InventorySuccess\Block\Adminhtml\TransferStock\Request\Edit\Button;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magestore\InventorySuccess\Model\TransferStock;
use Magestore\InventorySuccess\Api\Data\TransferStock\TransferPermission;

/**
 * Class SaveButton
 */
class Delete extends \Magestore\InventorySuccess\Block\Adminhtml\TransferStock\AbstractTransferStock
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
            if(($transferStock->getStatus() == TransferStock::STATUS_CANCEL)
                    && $this->_permissionManagement->checkPermission(TransferPermission::REQUEST_STOCK_EDIT_GENERAL, $this->getWarehouse())){
                return [
                    'label' => __('Delete'),
                    'class' => 'save primary',
                    'on_click' => '',
                    'data_attribute' => [
                        'mage-init' => [
                            'buttonAdapter' => [
                                'actions' => [
                                    [
                                        'targetName' => 'transferstock_request_form.transferstock_request_form',
                                        'actionName' => 'save',
                                        'params' => [
                                            true,
                                            [
                                                'id' => $this->getRequest()->getParam('id'),
                                                'action' => 'delete'
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
    }
}
