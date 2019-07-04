<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Api\Warehouse;

interface CreditmemoItemManagementInterface
{
    /**
     * Get Warehouse by creditmemo item id
     * 
     * @param int $creditmemoItemId
     * @return int
     */
    public function getWarehouseByCreditmemoItemId($creditmemoItemId);
  
}          