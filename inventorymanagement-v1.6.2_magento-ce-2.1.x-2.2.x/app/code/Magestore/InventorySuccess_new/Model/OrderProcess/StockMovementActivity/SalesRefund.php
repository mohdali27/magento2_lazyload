<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\OrderProcess\StockMovementActivity;

class SalesRefund extends \Magestore\InventorySuccess\Model\StockMovement\StockMovementActivity
{

    const STOCK_MOVEMENT_ACTION_CODE = 'sales_refund';
    const STOCK_MOVEMENT_ACTION_LABEL = 'Sales Refund';

    /**
     * Get action reference of stock movement
     *
     * @return string
     */
    public function getStockMovementActionReference($id = null)
    {
        return $this->objectManager->get('Magento\Sales\Model\Order\Creditmemo')
                        ->load($id)->getIncrementId();
    }

    /**
     * Get stock movement action URL
     *
     * @param $id
     * @return string|null
     */
    public function getStockMovementActionUrl($id = null)
    {
        return $this->getUrl('sales/creditmemo/view', ['creditmemo_id' => $id]);
    }

}
