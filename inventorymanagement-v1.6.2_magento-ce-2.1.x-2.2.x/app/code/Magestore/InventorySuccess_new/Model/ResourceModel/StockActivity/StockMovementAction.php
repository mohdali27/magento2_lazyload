<?php
/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magestore\InventorySuccess\Model\ResourceModel\StockActivity;
use Magestore\InventorySuccess\Api\Db\QueryProcessorInterface;


class StockMovementAction extends \Magestore\InventorySuccess\Model\ResourceModel\AbstractResource
{
    /**
     *
     * @param QueryProcessorInterface $queryProcessor
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string $connectionName
     */
    public function __construct(
        QueryProcessorInterface $queryProcessor,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $connectionName = null
    ) {
        parent::__construct($queryProcessor, $context, $connectionName);
    }
    
    protected function _construct()
    {
    }
    
    public function addStockMovements($data = []){
        $this->_queryProcessor->start();
        if(count($data)>0){
            $this->_prepareAddStockMovement($data);
        }
        $this->_queryProcessor->process();
        return $this;
    }

    /**
     * Prepare to add new stock movements
     *
     * @param array $data Stock Movements Data
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChange
     */
    protected function _prepareAddStockMovement($data)
    {
        /* add query to the processor */
        $this->_queryProcessor->addQuery([
            'type' => QueryProcessorInterface::QUERY_TYPE_INSERT,
            'values' => $data,
            'table' => $this->getTable('os_stock_movement')
        ]);
        return $this;
    }


    /**
     * Prepare to update stock movements
     *
     * @param array $data
     * @return \Magestore\InventorySuccess\Model\ResourceModel\StockActivity\StockChange
     */
    public function updateStockMovements($data = []){

        $this->_queryProcessor->start();
        if(count($data)>0){
            /* add query to the processor */
            $this->_queryProcessor->addQuery([
                'type' => QueryProcessorInterface::QUERY_TYPE_UPDATE,
                'values' => $data['values'],
                'condition' => $data['condition'],
                'table' => $this->getTable('os_stock_movement'),
            ]);
        }
        $this->_queryProcessor->process();
        return $this;
    }

}