<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Observer\StockChange;

use Magento\Framework\Event\ObserverInterface;

class IssueStockAfter  extends StockMovementObserver implements ObserverInterface
{

    /**
     * Process issue data to add stock movement
     * 
     * @param $data
     * @return array
     */
    public function processData($data)
    {   
        $insertData = [];
        $productData = $this->_loadProductData(array_keys($data['products']));
        $actionNumber = $this->getActionNumber($data['action_type'], $data['action_id']);
        foreach ($data['products'] as $productId => $qty) {
            if($qty == 0) {
                continue;
            }
            $productSku = isset($productData[$productId]) ? $productData[$productId]['sku'] : 'N/A'; 
            $insertData[] = [
                'product_id' => $productId,
                'product_sku' => $productSku,
                'qty' => $qty,
                'action_code' => $data['action_type'],
                'action_id' => $data['action_id'],
                'action_number' => $actionNumber,
                'warehouse_id' => $data['warehouse_id']
            ];
        }
        return $insertData;
    }
    
    /**
     * Get Product Ids update updated time
     *
     * @param $data
     * @return array
     */
    public function getUpdateStockItemData($data)
    {
        $productIds = [];
        foreach ($data['products'] as $productId => $qty) {
            if($qty == 0) {
                continue;
            }
            $productIds[] = $productId;
        }
        return $productIds;
    }

}
