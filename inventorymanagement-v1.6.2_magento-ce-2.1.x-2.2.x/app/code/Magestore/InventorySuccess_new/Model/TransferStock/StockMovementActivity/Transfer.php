<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\TransferStock\StockMovementActivity;
use \Magestore\InventorySuccess\Api\Data\TransferStock\TransferStockInterface;

class Transfer extends \Magestore\InventorySuccess\Model\StockMovement\StockMovementActivity
{

    const STOCK_MOVEMENT_ACTION_CODE = 'transferstock';
    const STOCK_MOVEMENT_ACTION_LABEL = 'Transfer Stock';

    /**
     * Get action reference of stock movement
     *
     * @return string
     */
    public function getStockMovementActionReference($id = null)
    {
        return $this->objectManager->get('Magestore\InventorySuccess\Model\TransferStock')
                        ->load($id)->getTransferstockCode();
    }

    /**
     * Get stock movement action URL
     *
     * @param $id
     * @return string|null
     */
    public function getStockMovementActionUrl($id = null)
    {
        $transferStock = $this->objectManager->get('Magestore\InventorySuccess\Model\TransferStock')->load($id);
        $type = $transferStock->getType();

        switch ($type){
            case TransferStockInterface::TYPE_REQUEST:
                return $this->getUrl('inventorysuccess/transferstock_request/edit', ['id' => $id]);
                break;
            case TransferStockInterface::TYPE_SEND:
                return $this->getUrl('inventorysuccess/transferstock_send/edit', ['id' => $id]);
                break;
            case TransferStockInterface::TYPE_FROM_EXTERNAL:
                return $this->getUrl('inventorysuccess/transferstock_external/edit', ['id' => $id, 'type'=>'from_external']);
                break;
            case TransferStockInterface::TYPE_TO_EXTERNAL:
                return $this->getUrl('inventorysuccess/transferstock_external/edit', ['id' => $id, 'type'=>'to_external']);
                break;
        }
    }

}
