<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\StockActivity;

use Magestore\InventorySuccess\Api\StockActivity\StockMovementActionInterface;

class StockMovementAction implements StockMovementActionInterface
{
    /**
     * @var \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockMovementActionFactory
     */
    protected $resourceFactory;
    
    public function __construct(
        \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockMovementActionFactory $stockMovementActionFactory
    ){
        $this->resourceFactory = $stockMovementActionFactory;
    }
    
    /**
     * add a row into table os_stock_movement
     *
     * @param array $data
     * @return \Magestore\InventorySuccess\Api\Data\StockMovement\StockMovementInterface
     */
    public function addStockMovementAction($data = []){
        return $this->getResource()->addStockMovements($data);
    }

    /**
     *
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockMovementActionFactory
     */
    public function getResource()
    {
        return $this->resourceFactory->create();
    }
}