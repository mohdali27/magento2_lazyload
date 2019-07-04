<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Ui\DataProvider\Sales\ShipmentForm\Modifier\RequestStock;

/**
 * Class Related
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class General extends \Magestore\InventorySuccess\Ui\DataProvider\TransferStock\Request\Form\Modifier\General
{  
    
    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        /* set default transfer stock reason */
        $reason = '';
        if($orderId = $this->request->getParam('order_id')) {
            $reason = __('Request stocks to fulfill Sales #%1', $orderId);
        }
         /* set default destination warehouse Id */
        if($desWarehouseId = $this->request->getParam('des_warehouse_id')) {
            $meta = array_replace_recursive(
                $meta,
                [
                    $this->_groupContainer => [
                        'children' => [
                            'des_warehouse_id' =>[
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'default' => $desWarehouseId,
                                        ],
                                    ],
                                ],
                            ],      
                            'reason' =>[
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'default' => $reason,
                                        ],
                                    ],
                                ],
                            ],                               
                        ],
                    ],
                ]
            );
        }
        return $meta;
    }    
    
}